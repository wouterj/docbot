<?php

namespace Stoffer\Core\ServiceContainer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ExtensionResolver
{
    public function resolve($name)
    {
        if (!(
            // name is the FQCN
            class_exists($class = $name)
            // name is the shortcut (e.g. StofferCore for Stoffer\CoreExtension\ServiceContainer\Extension)
            || (
                preg_match('/^([A-Z][a-z]*)([A-Z][a-z]+)$/', $name, $data)
                && class_exists($class = $data[1].'\\'.$data[2].'Extension\\ServiceContainer\\Extension')
            )
            // special case for the core extension
            || ('StofferCore' === $name && $class = 'Stoffer\ServiceContainer\Extension')
        )) {
            throw new \InvalidArgumentException(sprintf('Cannot find the class for extension name "%s".', $name));
        }

        $reflection = new \ReflectionClass($class);
        if (!$reflection->implementsInterface('Symfony\Component\DependencyInjection\Extension\ExtensionInterface')) {
            throw new \LogicException(sprintf('Extension class "%s" does not implement the ExtensionInterface.', $class));
        }

        return $reflection->newInstance();
    }
}