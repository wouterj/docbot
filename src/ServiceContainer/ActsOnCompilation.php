<?php

namespace Stoffer\ServiceContainer;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
interface ActsOnCompilation
{
    public function compile(ContainerBuilder $container);
}
