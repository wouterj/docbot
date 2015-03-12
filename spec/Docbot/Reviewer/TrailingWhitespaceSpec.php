<?php

namespace spec\Docbot\Reviewer;

use Docbot\Event\RequestFileReview;
use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use spec\helpers\Promise\FileReviewEvent as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;

class TrailingWhitespaceSpec extends ReviewerBehaviour
{
    function it_finds_lines_with_only_whitespace(EventManagerInterface $eventManager, RequestFileReview $event)
    {
        PromiseThatEvent::willHaveFile($event, Text::fromArray(array(
            'a file with just normal text',
            '     ',
            'followed by nasty trailing whitespace',
            '',
            'and a simple empty line.',
        )));

        PredictThatReviewer::shouldReportError($eventManager, 'There should be no trailing whitespace at the end of a line', 2);

        $this->review($event);
    }

    function it_finds_trailing_whitespace_at_the_end_of_a_line(EventManagerInterface $eventManager, RequestFileReview $event)
    {
        PromiseThatEvent::willHaveFile($event, Text::fromArray(array(
            'a line with whitespace after it    ',
            "a tab is also whitespace\t",
        )));

        PredictThatReviewer::shouldReportError($eventManager, 'There should be no trailing whitespace at the end of a line', 1);
        PredictThatReviewer::shouldReportError($eventManager, 'There should be no trailing whitespace at the end of a line', 2);

        $this->review($event);
    }
}
