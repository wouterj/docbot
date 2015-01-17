<?php

namespace Stoffer\ServiceContainer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class CliExtension extends Extension
{
    private $input;
    private $output;

    function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function load(array $config, ContainerBuilder $container)
    {
        $container->setParameter('cli.output', $this->output);
        $container->setParameter('cli.input', $this->input);
    }
}
