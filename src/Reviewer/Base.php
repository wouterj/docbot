<?php

namespace Docbot\Reviewer;

use Docbot\Editor;
use Docbot\Event\ReportError;
use Docbot\Event\RequestFileReview;
use Docbot\Reviewer;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class Base implements Reviewer
{
    /** @var EventManagerInterface */
    private $eventManager;

    public function review(RequestFileReview $event)
    {
        $event->getFile()->map(array($this, 'reviewLine'));
    }

    abstract public function reviewLine($line, $lineNumber, $file);

    protected function reportError($message, $line, $filename, $lineNumber)
    {
        $this->getEventManager()->trigger(new ReportError($message, $line, $lineNumber, $filename));
    }

    /**
     * {@inheritDocs}
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritDocs}
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->eventManager = new EventManager(array('reviewer', get_class($this)));
        }

        return $this->eventManager;
    }

}
