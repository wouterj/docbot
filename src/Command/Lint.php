<?php

namespace Docbot\Command;

use Docbot\ServiceContainer\Extension as DocbotExtension;
use Gnugat\Redaktilo\EditorFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('path');
        foreach ($paths as $path) {
            if (is_file($path)) {
                $this->lintFile($path);
            } else {
                $this->lintDirectory($path, $input->getOption('ignore'));
            }
        }
    }

    private function lintFile($path)
    {
        if ($path instanceof \SplFileInfo) {
            $path = $path->getPathname();
        }

        $container = $this->getContainer();

        $file = EditorFactory::createEditor()->open($path);
        $violations = $container->get(DocbotExtension::DOCBOT_ID)->lint($file);

        return $container->get('reporter')->handle($violations, $file);
    }

    private function lintDirectory($path, $ignore = null)
    {
        $files = Finder::create()->files()->in($path);
        if (null !== $ignore) {
            $files->notName($ignore);
        }

        foreach ($files as $file) {
            $this->lintFile($file);
        }
    }

    private function getContainer()
    {
        return $this->getApplication()->getContainer();
    }
}
