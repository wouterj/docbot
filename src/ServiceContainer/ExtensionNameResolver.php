<?php

namespace Docbot\ServiceContainer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ExtensionNameResolver
{
    public function resolve($name)
    {
        if ($this->nameIsClassName($name)) {
            $class = $name;
        } elseif ($this->nameIsShortCut($name)) {
            $class = $this->getClassNameFromShortcut($name);
        } elseif ($this->nameIsCoreExtension($name)) {
            $class = 'Docbot\ServiceContainer\Extension';
        } else {
            throw new \InvalidArgumentException(sprintf('Cannot find the class for extension name "%s".', $name));
        }

        return $this->castToExtensionType($class);
    }

    private function nameIsClassName($name)
    {
        return class_exists($name);
    }

    private function nameIsShortCut($name)
    {
        $class = $this->getClassNameFromShortcut($name);

        if (!$class) {
            return false;
        }

        return class_exists($class);
    }

    private function getClassNameFromShortcut($name)
    {
        if (!preg_match('/^([A-Z][a-z]*)([A-Z][a-z]+)$/', $name, $data)) {
            return false;
        }

        return $data[1].'\\'.$data[2].'Extension\\ServiceContainer\\Extension';
    }

    private function nameIsCoreExtension($name)
    {
        return 'StofferCore' === $name;
    }

    /**
     * @param $class
     *
     * @return \Symfony\Component\DependencyInjection\Extension\ExtensionInterface
     */
    private function castToExtensionType($class)
    {
        $reflection = new \ReflectionClass($class);
        if (!$reflection->implementsInterface('Symfony\Component\DependencyInjection\Extension\ExtensionInterface')) {
            throw new \LogicException(
                sprintf('Extension class "%s" does not implement the ExtensionInterface.', $class)
            );
        }

        return $reflection->newInstance();
    }
}
