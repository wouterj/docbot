<?php

/*
 * This file is part of the Docbot package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Docbot;

use Docbot\ServiceContainer\CliExtension;
use Docbot\ServiceContainer\ContainerFactory;
use Docbot\ServiceContainer\ExtensionNameResolver;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The CLI application of DocBot.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Application extends BaseApplication
{
    /** @var \Symfony\Component\DependencyInjection\Container|null */
    private $container;
    private $initialized = false;

    public function __construct()
    {
        parent::__construct('Docbot', '0.2-dev');
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->initializeIfNeeded($input, $output);

        return parent::doRun($input, $output);
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption(
                'extensions',
                'e',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'The extensions to enable',
                array('DocbotCore')
            )
        );

        return $definition;
    }

    private function initializeIfNeeded(InputInterface $input, OutputInterface $output)
    {
        if (!$this->initialized) {
            $this->initializeContainer($input, $output);
            $this->initializeCommands();

            $this->initialized = true;
        }
    }

    private function initializeContainer(InputInterface $input, OutputInterface $output)
    {
        $this->container = $this->buildContainer($input, $output);
    }

    private function initializeCommands()
    {
        foreach ($this->container->getParameter('console.commands') as $command) {
            $this->add($this->container->get($command));
        }
    }

    private function buildContainer(InputInterface $input, OutputInterface $output)
    {
        $factory = new ContainerFactory();
        $resolver = new ExtensionNameResolver();

        foreach ($input->getParameterOption(array('extensions', 'e'), array()) as $extension) {
            $factory->addExtension($resolver->resolve($extension));
        }

        $factory->addExtension(new CliExtension($input, $output));

        return $factory->createContainer();
    }
}
