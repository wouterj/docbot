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
    private $file;
    private $line;
    private $lineNumber;

    public function review(RequestFileReview $event)
    {
        $event->getFile()->map(array($this, 'doReviewLine'));
    }

    public function doReviewLine($line, $lineNumber, $file)
    {
        if ($file !== $this->file) {
            $this->file = $file;
        }
        $this->line = $line;
        $this->lineNumber = $lineNumber + 1;

        return $this->reviewLine($line, $lineNumber, $file);
    }

    abstract public function reviewLine($line, $lineNumber, $file);

    protected function reportError($message, $lineNumber = null, $line = null)
    {
        $this->getEventManager()->trigger(new ReportError(
            $message,
            $line ?: $this->line,
            $lineNumber ?: $this->lineNumber,
            $this->file
        ));
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
