<?php

namespace Stoffer\Lint\Reviewer;

use Stoffer\Lint\Editor;
use Stoffer\Lint\Reviewer;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class Base implements Reviewer
{
    /** @var EventManagerInterface */
    private $eventManager;

    public function review(Event $event)
    {
        Editor::map($event->getParams()->file, array($this, 'reviewLine'));
    }

    abstract public function reviewLine($line, $lineNumber, $file);

    protected function reportError($message, $line, $lineNumber)
    {
        $params = new \stdClass();
        $params->message = $message;
        $params->line = $line;
        $params->lineNumber = $lineNumber;

        $this->getEventManager()->trigger('error_reported', 'reviewer', $params);
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