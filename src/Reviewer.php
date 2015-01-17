<?php

namespace Stoffer;

use Zend\EventManager\Event;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventsCapableInterface;

/**
 * A reviewer reviews the file contents and reports errors.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface Reviewer extends EventsCapableInterface, EventManagerAwareInterface
{
    public function review(Event $event);
}
