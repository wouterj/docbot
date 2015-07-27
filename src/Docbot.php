<?php

namespace Docbot;

use Docbot\Reviewer\Check;
use Gnugat\Redaktilo\Text;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\CS\Fixer;

/**
 * The main access point for reviewing jobs.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Docbot extends Fixer
{
    public function __construct()
    {
        parent::__construct();
        
        $this->setEventDispatcher(new EventDispatcher());
    }

    public function listen($event, callable $listener, $priority = 0)
    {
        $this->eventDispatcher->addListener($event, $listener, $priority);
    }
}
