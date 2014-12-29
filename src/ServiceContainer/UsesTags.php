<?php

namespace Stoffer\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
interface UsesTags
{
    public function compile(ContainerBuilder $container);
}
