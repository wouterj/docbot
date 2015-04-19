<?php

namespace Docbot\Command;

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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('path');
        foreach ($paths as $path) {
            if (is_file($path)) {
                $this->lintFile($path, $input->getOption('types'));
            } else {
                $this->lintDirectory($path, $input->getOption('types'), $input->getOption('ignore'));
            }
        }
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
        $files = Finder::create()->files()->in($path);
        if (null !== $ignore) {
            $files->notName($ignore);
        }

        foreach ($files as $file) {
            $this->lintFile($file, $types);
        }
    }

    /** @return Container */
    private function getContainer()
    {
        return $this->getApplication()->getContainer();
    }
}
