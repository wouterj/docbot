<?php

namespace Stoffer;

use Stoffer\Event\RequestFileReview;
use Stoffer\Reviewer;
use Gnugat\Redaktilo\Text;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\SharedEventManager;
use Zend\EventManager\SharedEventManagerInterface;

/**
 * The main access point for reviewing jobs.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Stoffer
{
    /** @var EventManagerInterface */
    private $eventManager;

    public function __construct(SharedEventManagerInterface $eventManager = null)
    {
        $this->eventManager = new EventManager();
        $this->eventManager->setIdentifiers(array('stoffer'));

        $this->eventManager->setSharedManager($eventManager ?: new SharedEventManager());
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * A helper function to attach the reviewer to the correct events
     */
    public function addReviewer(Reviewer $reviewer, $priority = 0)
    {
        $this->eventManager->attach('file_review_requested', array($reviewer, 'review'), $priority);
        $reviewer->getEventManager()->setSharedManager($this->eventManager->getSharedManager());
    }

    public function lint(Text $file)
    {
        $this->eventManager->trigger(new RequestFileReview($file));
    }
}
