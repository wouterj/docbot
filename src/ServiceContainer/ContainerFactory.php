<?php

namespace Docbot\ServiceContainer;

use Docbot\ServiceContainer\Compiler\InlineCompilePass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Creates the container including all core services.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ContainerFactory
{
    private $extensions = array(DocbotExtension::class);
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
            if (!is_object($extension)) {
                $extension = new $extension();
            }

            $container->registerExtension($extension);
            // make sure extension is loaded
            $container->loadFromExtension($extension->getAlias(), array());
        }

        foreach ($this->passes as $pass) {
            $container->addCompilerPass($pass);
        }

        $container->addCompilerPass(new InlineCompilePass());

        $container->compile();

        return $container;
    }
}
