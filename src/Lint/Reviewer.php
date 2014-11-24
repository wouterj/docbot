<?php

namespace Stoffer\Lint;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventsCapableInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
interface Reviewer extends EventsCapableInterface, EventManagerAwareInterface
{
    public function review(Event $event);
} 