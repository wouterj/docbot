<?php

namespace Docbot\Command;

use Docbot\Reporter;
use Docbot\ServiceContainer\Extension as DocbotExtension;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Gnugat\Redaktilo\EditorFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;

/**
 * This commands lints documentation files.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Lint extends Command
{
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
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $level = Reporter::VERBOSITY_ERROR;

        if ($output->isQuiet()) {
            $level = Reporter::VERBOSITY_NONE;
        } elseif ($output->isVeryVerbose()) {
            $level = Reporter::VERBOSITY_ALL;
        }

        $this->getContainer()->get('reporter')->setVerbosity($level);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = Reporter::UNKNOWN;
        $paths = $input->getArgument('path');
        foreach ($paths as $path) {
            if (is_file($path)) {
                $result = $this->lintFile($path, $input->getOption('types'));
            } else {
                $result = $this->lintDirectory($path, $input->getOption('types'), $input->getOption('ignore'));
            }
        }

        if (!$output->isQuiet()) {
            $output->writeln(array(
                '',
                sprintf(
                    '<bg=%s>%-'.reset($this->getApplication()->getTerminalDimensions()).'s</>',
                    $result === Reporter::SUCCESS ? 'green' : 'red',
                    $result === Reporter::SUCCESS
                        ? '[OK] All documents are perfect!'
                        : '[ERROR] Sorry, some errors were found'
                ),
                '',
            ));
        }

        return $result > -1 ? $result : 3;
    }

    private function lintFile($path, array $types)
    {
        if ($path instanceof \SplFileInfo) {
            $path = $path->getPathname();
        }

        $container = $this->getContainer();

        $file = EditorFactory::createEditor()->open($path);
        $violations = $container->get(DocbotExtension::DOCBOT_ID)->lint($file, $types);

        return $container->get('reporter')->handle($violations, $file);
    }

    private function lintDirectory($path, array $types, $ignore = null)
    {
        $result = Reporter::UNKNOWN;

        $files = Finder::create()->files()->in($path);
        if (null !== $ignore) {
            $files->notName($ignore);
        }

        foreach ($files as $file) {
            $r = $this->lintFile($file, $types);
            if ($r > $result) {
                $result = $r;
            }
        }

        return $result;
    }

    /** @return Container */
    private function getContainer()
    {
        return $this->getApplication()->getContainer();
    }
}
