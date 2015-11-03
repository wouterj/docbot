<?php

namespace Docbot\ServiceContainer;

use Docbot\Docbot;
use Docbot\Fixer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\CS\Config\Config;

/**
 * The core extension.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class DocbotExtension extends Extension
{
    const DOCBOT_ID = 'docbot';
    const CONFIG_ID = 'docbot.config';

    /** {@inheritdoc} */
    public function load(array $config, ContainerBuilder $container)
    {
        $this->loadServices($container);
        $this->loadCommands($container);
    }

    private function loadServices(ContainerBuilder $container)
    {
        $container->register(self::DOCBOT_ID, Docbot::class);

        $definition = new Definition(Config::class);
        $definition
            ->addMethodCall('setUsingCache', [false])
            ->addMethodCall('fixers', [[
                new Definition(Fixer\EmptyFixer::class),
//                new Definition(Fixer\DirectiveWhitespaceFixer::class),
//                new Definition(Fixer\TrailingWhitespaceFixer::class),
//                new Definition(Fixer\LineLengthFixer::class),
//                new Definition(Fixer\SerialCommaFixer::class),
//                new Definition(Fixer\ShortPhpSyntaxFixer::class),
//                new Definition(Fixer\TitleLevelFixer::class),
//                new Definition(Fixer\TitleUnderlineFixer::class),
//                new Definition(Fixer\UnstyledDirectivesFixer::class),
            ]])
        ;
        $container->setDefinition(self::CONFIG_ID, $definition);
    }

    private function loadCommands(ContainerBuilder $container)
    {
        $definition = new Definition('Docbot\Command\Fix');
        $definition->addTag(CliExtension::COMMAND_TAG);//
        $container->setDefinition('command.lint', $definition);
    }

    public function getAlias()
    {
        return 'docbot';
    }
}
