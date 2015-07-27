<?php

namespace Docbot\ServiceContainer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

/**
 * A special extension taking care of console input/output services.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class CliExtension extends Extension implements CompilerPassInterface
{
    const INPUT_ID = 'cli.input';
    const OUTPUT_ID = 'cli.output';

    const COMMAND_TAG = 'command';

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

    /** {@inheritdoc} */
    public function process(ContainerBuilder $container)
    {
        $this->registerCommands($container);
    }
    
    private function registerCommands(ContainerBuilder $container)
    {
        if ($container->hasParameter('console.commands')) {
            $commands = array();
        } else {
            $commands = array();
        }

        $commandServiceIds = array_keys($container->findTaggedServiceIds(self::COMMAND_TAG));
        foreach ($commandServiceIds as $commandServiceId) {
            $commands[] = $commandServiceId;
        }

        $container->setParameter('console.commands', $commands);
    }
}
