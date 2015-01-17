<?php

namespace Stoffer;

use Stoffer\ServiceContainer\CliExtension;
use Stoffer\ServiceContainer\ContainerFactory;
use Stoffer\ServiceContainer\Extension;
use Stoffer\ServiceContainer\ExtensionNameResolver;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The CLI application Stoffer.
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
        parent::__construct('Stoffer', '1.0-dev');
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
                array('StofferCore')
            )
        );
        $definition->addOption(
            new InputOption(
                'types',
                't',
                InputOption::VALUE_REQUIRED,
                'The reviewer types (available: rst, doc, symfony)',
                'rst, doc, symfony'
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

        $factory->addConfigFor('stoffer', array(
            'reviewers' => array(
                'types' => array_map('trim', explode(',', $input->getParameterOption(array('--types', '-t'), 'rst, doc, symfony'))),
            ),
        ));

        return $factory->createContainer();
    }
}
