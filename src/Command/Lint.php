<?php

namespace Stoffer\Command;

use Gnugat\Redaktilo\EditorFactory;
use \Stoffer\Reporter\Console;
use \Stoffer\Reviewer\LineLength;
use \Stoffer\Stoffer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
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
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Where to put the output (defaults to cli)', 'cli')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('path');
        foreach ($paths as $path) {
            if (is_file($path)) {
                $this->lintFile($path);
            } else {
                $this->lintDirectory($path);
            }
        }
    }

    private function lintFile($path)
    {
        if ($path instanceof \SplFileInfo) {
            $path = $path->getPathname();
        }

        $this->getContainer()->get('stoffer')->lint(EditorFactory::createEditor()->open($path));
    }

    private function lintDirectory($path)
    {
        foreach (Finder::create()->files()->in($path) as $file) {
            $this->lintFile($file);
        }
    }

    private function getContainer()
    {
        return $this->getApplication()->getContainer();
    }
}
