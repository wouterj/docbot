<?php

namespace Stoffer\Core\ServiceContainer;

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
