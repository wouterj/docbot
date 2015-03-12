<?php

namespace Docbot\ServiceContainer;

use Docbot\ServiceContainer\Compiler\InlineCompilePass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ContainerFactory
{
    private $extensions = array('Docbot\ServiceContainer\Extension');
    private $passes = array();
    private $config = array();

    public function addCompiler(CompilerPassInterface $compilerPass)
    {
        $this->passes[] = $compilerPass;
    }

    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    public function addConfigFor($name, array $config)
    {
        if (!isset($this->config[$name])) {
            $this->config[$name] = array();
        }

        $this->config[$name] = array_merge($this->config[$name], $config);
    }

    public function createContainer()
    {
        $container = new ContainerBuilder();

        foreach ($this->extensions as $extension) {
            if (!is_object($extension)) {
                $extension = new $extension();
            }

            $container->registerExtension($extension);
            $container->loadFromExtension($extension->getAlias(), array());
        }

        foreach ($this->passes as $pass) {
            $container->addCompilerPass($pass);
        }

        $container->addCompilerPass(new InlineCompilePass());

        foreach ($this->config as $name => $config) {
            $container->loadFromExtension($name, $config);
        }

        $container->compile();

        return $container;
    }
}
