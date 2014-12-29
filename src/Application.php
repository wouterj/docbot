<?php

namespace Stoffer;

use Stoffer\ServiceContainer\ContainerFactory;
use Stoffer\ServiceContainer\Extension;
use Stoffer\ServiceContainer\ExtensionResolver;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Application extends BaseApplication
{
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
        $this->container = $this->buildContainer($input);

        if (!$this->initialized) {
            foreach ($this->container->getParameter('console.commands') as $command) {
                $this->add($this->container->get($command));
            }

            $this->initialized = true;
        }

        return parent::doRun($input, $output);
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(
            new InputOption(
                'filters',
                'f',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'The extensions to enable',
                array('StofferCore')
            )
        );

        return $definition;
    }

    private function buildContainer(InputInterface $input)
    {
        $factory = new ContainerFactory();
        $resolver = new ExtensionResolver();

        foreach ($input->getParameterOption(array('filter', 'f'), array()) as $extension) {
            $factory->addExtension($resolver->resolve($extension));
        }

        return $factory->createContainer();
    }
}
