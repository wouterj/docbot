<?php

namespace Docbot\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension as Base;
use Symfony\Component\DependencyInjection\Reference;

/**
 * The core extension.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Extension extends Base implements ActsOnCompilation
{
    const DOCBOT_ID = 'docbot';
    const REVIEWER_TAG = 'reviewer';
    const SHARED_EVENT_MANAGER_ID = 'event_manager';
    const LISTENER_TAG = 'event_listener';
    const COMMAND_TAG = 'command';

    /**
     * {@inheritDocs}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $config = call_user_func_array('array_merge_recursive', array_merge(
            array('reviewers' => array('types' => array())),
            $config
        ));

        $this->loadServices($container);
        $this->loadCommands($container);
        $this->loadReviewers($container, $config['reviewers']['types']);
    }

    private function loadServices(ContainerBuilder $container)
    {
        $container->setDefinition(self::DOCBOT_ID, new Definition('Docbot\Docbot', array(
            new Reference(self::SHARED_EVENT_MANAGER_ID),
        )));

        $container->register(self::SHARED_EVENT_MANAGER_ID, 'Zend\EventManager\SharedEventManager');

        $container->register('reporter.console', 'Docbot\Reporter\Console')
            ->addArgument('%cli.output%')
            ->addTag(self::LISTENER_TAG, array(
                'target' => 'reviewer',
                'event' => 'error_reported',
                'method' => 'collectErrors',
            ))
            ->addTag(self::LISTENER_TAG, array(
                'target' => 'docbot',
                'event' => 'file_review_requested',
                'method' => 'printFileName',
                'priority' => 999,
            ))
            ->addTag(self::LISTENER_TAG, array(
                'target' => 'docbot',
                'event' => 'file_review_requested',
                'method' => 'printReport',
                'priority' => -999,
            ))
        ;
    }

    protected function loadCommands(ContainerBuilder $container)
    {
        $definition = new Definition('Docbot\Command\lint');
        $definition->addTag(self::COMMAND_TAG);
        $container->setDefinition('command.lint', $definition);
    }

    protected function loadReviewers(ContainerBuilder $container, array $types)
    {
        foreach ($types as $type) {
            $this->{'load'.ucfirst($type).'Reviewers'}($container);
        }
    }

    protected function loadSymfonyReviewers(ContainerBuilder $container)
    {
        $container->register('reviewer.unstyled_admonitions', 'Docbot\Reviewer\UnstyledAdmonitions')->addTag(self::REVIEWER_TAG);
        $container->register('reviewer.shortphp_syntax', 'Docbot\Reviewer\ShortPhpSyntax')->addTag(self::REVIEWER_TAG);
    }

    protected function loadDocReviewers(ContainerBuilder $container)
    {
        $container->register('reviewer.title_case', 'Docbot\Reviewer\TitleCase')->addTag(self::REVIEWER_TAG);
        $container->register('reviewer.first_person', 'Docbot\Reviewer\FirstPerson')->addTag(self::REVIEWER_TAG);
        $container->register('reviewer.title_level', 'Docbot\Reviewer\TitleLevel')->addTag(self::REVIEWER_TAG);
        $container->register('reviewer.line_length', 'Docbot\Reviewer\LineLength')->addTag(self::REVIEWER_TAG);
    }

    protected function loadRstReviewers(ContainerBuilder $container)
    {
        $container->register('reviewer.trailing_whitespace', 'Docbot\Reviewer\TrailingWhitespace')->addTag(self::REVIEWER_TAG);
        $container->register('reviewer.faulty_literals', 'Docbot\Reviewer\FaultyLiterals')->addTag(self::REVIEWER_TAG);
        $container->register('reviewer.directive_whitespace', 'Docbot\Reviewer\DirectiveWhitespace')->addTag(self::REVIEWER_TAG);
        $container->register('reviewer.title_underline', 'Docbot\Reviewer\TitleUnderline')->addTag(self::REVIEWER_TAG);
    }

    /**
     * {@inheritDocs}
     */
    public function compile(ContainerBuilder $container)
    {
        $this->registerCommands($container);
        $this->registerReviewers($container);
        $this->registerListeners($container);
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
        $definition = $container->getDefinition(self::DOCBOT_ID);

        $reviewers = $container->findTaggedServiceIds(self::REVIEWER_TAG);
        foreach ($reviewers as $id => $tags) {
            $definition->addMethodCall('addReviewer', array(new Reference($id)));
        }
    }

    public function getAlias()
    {
        return 'docbot';
    }
}
