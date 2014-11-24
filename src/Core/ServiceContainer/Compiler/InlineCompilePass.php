<?php

namespace Stoffer\Core\ServiceContainer\Compiler;

use Stoffer\Core\ServiceContainer\UsesTags;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class InlineCompilePass implements CompilerPassInterface
{
    /**
     * {@inheritDocs}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getExtensions() as $extension) {
            if (!$extension instanceof UsesTags) {
                continue;
            }

            $extension->compile($container);
        }
    }
} 