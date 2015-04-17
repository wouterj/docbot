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
class FaultyLiteralsSpec extends ReviewerBehaviour
{
    function it_finds_single_backtick_usage(RequestFileReview $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveFile($event, Text::fromString('This is probably some `wrong` backtick usage.'));

        PredictThatReviewer::shouldReportError(
            $eventManager,
            'Found unrecognized usage of backticks. Did you mean to create a link (`wrong`_) or a literal (``wrong``)?',
            1
        );

        $this->review($event);
    }
}
