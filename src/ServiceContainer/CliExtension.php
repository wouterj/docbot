<?php

namespace Docbot\ServiceContainer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension as Base;
use Symfony\Component\DependencyInjection\Reference;

/**
 * A special extension taking care of console input/output services.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class CliExtension extends Base
{
    const INPUT_ID = 'cli.input';
    const OUTPUT_ID = 'cli.output';

    private $input;
    private $output;

    function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function load(array $config, ContainerBuilder $container)
    {
        $container->register(self::OUTPUT_ID, $this->output);
        $container->register(self::INPUT_ID, $this->input);


        $this->loadReporters($container);
    }

    private function loadReporters(ContainerBuilder $container)
    {
        $definition = new Definition('Docbot\Reporter\Console', array(new Reference(self::OUTPUT_ID)));
        $container->setDefinition('reporter.console', $definition);

        $container->setAlias('reporter', new Alias('reporter.console'));
    }
}
