<?php

namespace spec\Docbot\Reviewer;

use Docbot\Event\RequestFileReview;
use Gnugat\Redaktilo\Text;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Promise\FileReviewEvent as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class SerialCommaSpec extends ReviewerBehaviour
{
    function it_finds_serial_commas(RequestFileReview $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveFile($event, Text::fromString('One has foo, bar, and cat.'));

        PredictThatReviewer::shouldReportError($eventManager, 'Serial (Oxford) comma\'s should be avoided: "[...] bar and [...]"', 1);

        $this->review($event);
    }
}
