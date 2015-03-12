<?php

namespace spec\Docbot\Reviewer;

use Docbot\Event\RequestFileReview;
use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Promise\FileReviewEvent as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class ShortPhpSyntaxSpec extends ReviewerBehaviour
{
    function it_finds_places_where_short_syntax_should_be_used(RequestFileReview $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveFile($event, Text::fromArray(array(
            'A file ending with a nice colon:',
            '',
            '.. code-block:: php',
        )));

        PredictThatReviewer::shouldReportError($eventManager, 'The short syntax for PHP code (::) should be used here', 3);

        $this->review($event);
    }

    function it_does_not_error_on_correct_long_syntax_usages(RequestFileReview $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveFile($event, Text::fromArray(array(
                'A file without a colon.',
                '',
                '.. code-block:: php',
        )));

        PredictThatReviewer::shouldNotReportAnyError($eventManager);

        $this->review($event);
    }

    function it_does_ignore_whitespace(RequestFileReview $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveFile($event, Text::fromArray(array(
            '    A line ending with a colon:   ',
            '',
            '',
            "\t",
            '    .. code-block:: php'
        )));

        PredictThatReviewer::shouldReportError($eventManager, 'The short syntax for PHP code (::) should be used here', 5);

        $this->review($event);
    }
}
