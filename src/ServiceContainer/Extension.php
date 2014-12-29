<?php

namespace Stoffer\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension as Base;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Extension extends Base implements UsesTags
{
    const STOFFER_ID = 'stoffer';
    const REVIEWER_TAG = 'reviewer';
    const SHARED_EVENT_MANAGER_ID = 'event_manager';
    const LISTENER_TAG = 'event_listener';

    /**
     * {@inheritDocs}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $this->loadServices($container);
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

    public function compile(ContainerBuilder $container)
    {
        $this->registerCommands($container);
        $this->registerReviewers($container);
        $this->registerListeners($container);
    }

    private function loadServices(ContainerBuilder $container)
    {
        $container->setDefinition(self::STOFFER_ID, new Definition('Stoffer\Core\Stoffer', array(
            new Reference(self::SHARED_EVENT_MANAGER_ID),
        )));

        $container->register(self::SHARED_EVENT_MANAGER_ID, 'Zend\EventManager\SharedEventManager');

        $container->register('reporter.console', 'Stoffer\Core\Reporter\Console')
            ->addTag(self::LISTENER_TAG, array(
                'target' => 'reviewer',
                'event' => 'error_reported',
                'method' => 'collectErrors',
            ))
            ->addTag(self::LISTENER_TAG, array(
                'target' => 'stoffer',
                'event' => 'file_review_requested',
                'method' => 'printFileName',
                'priority' => 999,
            ))
            ->addTag(self::LISTENER_TAG, array(
                'target' => 'stoffer',
                'event' => 'file_review_requested',
                'method' => 'printReport',
                'priority' => -999,
            ))
        ;
    }

    private function registerCommands(ContainerBuilder $container)
    {
        if ($container->hasParameter('console.commands')) {
            $commands = array();
        } else {
            $commands = array();
        }

        foreach (array_keys($container->findTaggedServiceIds('command')) as $command) {
            $commands[] = $command;
        }

        $container->setParameter('console.commands', $commands);
    }

    private function registerListeners(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(self::SHARED_EVENT_MANAGER_ID);

        $listeners = $container->findTaggedServiceIds(self::LISTENER_TAG);
        foreach ($listeners as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('attach', array(
                    $attributes['target'],
                    $attributes['event'],
                    array(new Reference($id), $attributes['method']),
                    isset($attributes['priority']) ? $attributes['priority'] : 1,
                ));
            }
        }
    }

    private function registerReviewers(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(self::STOFFER_ID);

        $reviewers = $container->findTaggedServiceIds(self::REVIEWER_TAG);
        foreach ($reviewers as $id => $tags) {
            $definition->addMethodCall('addReviewer', array(new Reference($id)));
        }
    }

    public function getAlias()
    {
        return 'stoffer';
    }
}
