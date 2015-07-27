<?php

namespace Docbot\Command;

use Docbot\Docbot;
use Docbot\ServiceContainer\DocbotExtension as DocbotExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\CS\FixerFileProcessedEvent;

/**
 * This commands lints documentation files.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Lint extends Command
{
    private $diff;
    /** @var Filesystem */
    private $filesystem;

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    public function configure()
    {
        $this->setName('lint')
            ->setDescription('Reports any problems found in the given files')
            ->addArgument('path', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'One file, list of files or a directory')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Where to put the output', 'cli')
            ->addOption('ignore', 'i', InputOption::VALUE_REQUIRED, 'Pattern to ignore files')
            ->addOption('types', 't', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'The reviewer types (available types: rst, doc, symfony)', array('rst', 'doc', 'symfony'))
            ->addOption('diff', 'd', InputOption::VALUE_REQUIRED, 'The diff to review')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = new Filesystem();
        
        if ($input->getOption('diff')) {
            $this->diff = $input->getOption('diff');
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);
        
        $style->title('Docbot\'s Review');
        
        $files = array();
        $directories = array();
        foreach ($input->getArgument('path') as $path) {
            if (null !== $path) {
                if (!$this->filesystem->isAbsolutePath($path)) {
                    $path = getcwd().DIRECTORY_SEPARATOR.$path;
                }
            }
            
            if (is_file($path)) {
                $files[] = $path;
            } elseif (null !== $path) {
                $directories[] = $path;
            }
        }
        
        /** @var Docbot $docbot */
        $docbot = $this->getContainer()->get(DocbotExtension::DOCBOT_ID);
        
        $config = $this->getContainer()->get(DocbotExtension::CONFIG_ID);
        if (0 === count($directories)) {
            $config->finder(new \ArrayIterator(array_map(function ($filename) {
                return new \SplFileInfo($filename);
            }, $files)));
        } elseif (0 === count($files)) {
            $config->finder(Finder::create()->files()->in($directories));
        } else {
            throw new \InvalidArgumentException(
                'Can only fix either one or more files, or one or more directories, but a mix of files and directories given'
            );
        }
        
        $docbot->listen(FixerFileProcessedEvent::NAME, function (FixerFileProcessedEvent $event) use ($style) {
            $statusString = $event->getStatusAsString();
            
            switch ($statusString) {
                case 'I':
                case 'E':
                    $style->write('<fg=red>'.$statusString.'</>');
                    break;
                    
                case '.':
                    $style->write('<fg=green>.</>');
                    break;
                    
                case 'F':
                    $style->write('<fg=yellow>F</>');
                    break;
                    
                default:
                    $style->write($statusString);
            }
        });
        
        $result = $docbot->fix($config);
        
        $output->writeln('');
        
        if ($output->isVeryVerbose()) {
            $output->writeln('');
            
            $style->section('Full Report');

            foreach ($result as $file => $r) {
                $output->writeln([
                    rtrim($this->filesystem->makePathRelative($file, getcwd()), '/\\'),
                    ''
                ]);

                foreach ($r['appliedFixers'] as $fixer) {
                    $output->writeln('- '.$fixer);
                }
            }
        }
    }

    /** @return Container */
    private function getContainer()
    {
        return $this->getApplication()->getContainer();
    }
}
