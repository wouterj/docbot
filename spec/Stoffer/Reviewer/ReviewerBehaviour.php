<?php

namespace spec\Stoffer\Reviewer;

use PhpSpec\ObjectBehavior;
use Zend\EventManager\EventManagerInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ReviewerBehaviour extends ObjectBehavior
{
    function let(EventManagerInterface $eventManager)
    {
        $this->setEventManager($eventManager);
    }
}
