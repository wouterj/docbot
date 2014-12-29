<?php

namespace Stoffer;

use Gnugat\Redaktilo\EditorFactory;
use Gnugat\Redaktilo\Text;
use \Stoffer\Reviewer;
use Zend\EventManager\EventManager;
use Zend\EventManager\SharedEventManager;
use Zend\EventManager\SharedEventManagerInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Stoffer
{
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

    public function addReviewer(Reviewer $reviewer, $priority = 0)
    {
        $this->eventManager->attach('file_review_requested', array($reviewer, 'review'), $priority);
        $reviewer->getEventManager()->setSharedManager($this->eventManager->getSharedManager());
    }

    public function lint(Text $file, $name)
    {
        $params = new \stdClass();
        $params->file = $file;
        $params->name = $name;

        $this->eventManager->trigger('file_review_requested', 'stoffer', $params);
    }
}
