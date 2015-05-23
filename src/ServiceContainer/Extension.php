<?php

namespace Docbot\ServiceContainer;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension as Base;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Validator\Validation;

/**
 * The core extension.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Extension extends Base implements CompilerPassInterface
{
    const DOCBOT_ID = 'docbot';
    const VALIDATOR_ID = 'validator';
    const COMMAND_TAG = 'command';

    /** {@inheritdoc} */
    public function load(array $config, ContainerBuilder $container)
    {
        $this->loadServices($container);
        $this->loadValidator($container);
        $this->loadReporters($container);
        $this->loadCommands($container);
    }

    private function loadServices(ContainerBuilder $container)
    {
        $definition = new Definition('Docbot\Docbot', array(new Reference(self::VALIDATOR_ID)));
        $container->setDefinition(self::DOCBOT_ID, $definition);
    }

    private function loadValidator(ContainerBuilder $container)
    {
        $container->register('validator.metadata_factory', 'Docbot\Validator\MetadataFactory');

        $definition = new Definition('Symfony\Component\Validator\ValidatorBuilder');
        $definition
            ->setFactory(array('Symfony\Component\Validator\Validation', 'createValidatorBuilder'))
            ->addMethodCall('setMetadataFactory', array(new Reference('validator.metadata_factory')))
            ->addMethodCall('setApiVersion', array(Validation::API_VERSION_2_5))
        ;
        $container->setDefinition('validator.builder', $definition);

        $definition = new Definition('Symfony\Component\Validator\Validator');
        $definition->setFactory(array(new Reference('validator.builder'), 'getValidator'));
        $container->setDefinition(self::VALIDATOR_ID, $definition);
    }

    private function loadReporters(ContainerBuilder $container)
    {
        $definition = new Definition('Docbot\Reporter\Console', array(new Reference(CliExtension::OUTPUT_ID)));
        $container->setDefinition('reporter.console', $definition);

        $container->setAlias('reporter', new Alias('reporter.console'));
    }

    private function loadCommands(ContainerBuilder $container)
    {
        $definition = new Definition('Docbot\Command\Lint');
        $definition->addTag(self::COMMAND_TAG);
        $container->setDefinition('command.lint', $definition);
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

    public function getAlias()
    {
        return 'docbot';
    }
}
