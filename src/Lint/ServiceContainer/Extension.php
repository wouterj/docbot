<?php

namespace Stoffer\Lint\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension as Base;
use Stoffer\Core\ServiceContainer\Extension as CoreExtension;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Extension extends Base
{
    public function load(array $config, ContainerBuilder $container)
    {
        $this->loadCommands($container);
        $this->loadReviewers($container);
    }

    protected function loadCommands(ContainerBuilder $container)
    {
        $definition = new Definition('Stoffer\Lint\Command\lint');
        $definition->addTag('command');
        $container->setDefinition('lint.command', $definition);
    }

    protected function loadReviewers(ContainerBuilder $container)
    {
        $container->register('reviewer.line_length', 'Stoffer\Lint\Reviewer\LineLength')->addTag(CoreExtension::REVIEWER_TAG);
        $container->register('reviewer.trailing_whitespace', 'Stoffer\Lint\Reviewer\TrailingWhitespace')->addTag(CoreExtension::REVIEWER_TAG);
        $container->register('reviewer.title_underline', 'Stoffer\Lint\Reviewer\TitleUnderline')->addTag(CoreExtension::REVIEWER_TAG);
        $container->register('reviewer.unstyled_admonitions', 'Stoffer\Lint\Reviewer\UnstyledAdmonitions')->addTag(CoreExtension::REVIEWER_TAG);
        $container->register('reviewer.title_case', 'Stoffer\Lint\Reviewer\TitleCase')->addTag(CoreExtension::REVIEWER_TAG);
        $container->register('reviewer.directive_whitespace', 'Stoffer\Lint\Reviewer\DirectiveWhitespace')->addTag(CoreExtension::REVIEWER_TAG);
        $container->register('reviewer.first_person', 'Stoffer\Lint\Reviewer\FirstPerson')->addTag(CoreExtension::REVIEWER_TAG);
        $container->register('reviewer.shortphp_syntax', 'Stoffer\Lint\Reviewer\ShortPhpSyntax')->addTag(CoreExtension::REVIEWER_TAG);
        $container->register('reviewer.title_level', 'Stoffer\Lint\Reviewer\TitleLevel')->addTag(CoreExtension::REVIEWER_TAG);
    }
}
