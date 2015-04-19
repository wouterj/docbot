<?php

namespace Docbot\ServiceContainer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class CliExtension extends Extension
{
    const INPUT_ID = 'cli.input';
    const OUTPUT_ID = 'cli.output';

    private $input;
    private $output;

    function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function load(array $config, ContainerBuilder $container)
    {
        $container->register(self::OUTPUT_ID, $this->output);
        $container->register(self::INPUT_ID, $this->input);
    }
}
