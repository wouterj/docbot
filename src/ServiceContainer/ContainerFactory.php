<?php

namespace Stoffer\ServiceContainer;

use Stoffer\ServiceContainer\Compiler\InlineCompilePass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ContainerFactory
{
    private $extensions = array('Stoffer\Core\ServiceContainer\Extension', 'Stoffer\Lint\ServiceContainer\Extension');
    private $passes = array();

    public function addCompiler(CompilerPassInterface $compilerPass)
    {
        $this->passes[] = $compilerPass;
    }

    public function addExtension(ExtensionInterface $extension)
    {
        $this->extensions[] = $extension;
    }

    public function createContainer()
    {
        $container = new ContainerBuilder();

        foreach ($this->extensions as $extension) {
            $e = new $extension();
            $container->registerExtension($e);
            $container->loadFromExtension($e->getAlias(), array());
        }

        foreach ($this->passes as $pass) {
            $container->addCompilerPass($pass);
        }

        $container->addCompilerPass(new InlineCompilePass());

        $container->compile();

        return $container;
    }
}
