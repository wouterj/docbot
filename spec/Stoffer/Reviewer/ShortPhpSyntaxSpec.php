<?php

namespace spec\Stoffer\Reviewer;

use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Promise\Event as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class ShortPhpSyntaxSpec extends ReviewerBehaviour
{
    function it_finds_places_where_short_syntax_should_be_used(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'A file ending with a nice colon:',
                '',
                '.. code-block:: php',
            ))
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'The short syntax for PHP code (::) should be used here', 3);

        $this->review($event);
    }

    function it_does_not_error_on_correct_long_syntax_usages(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'A file without a colon.',
                '',
                '.. code-block:: php',
            ))
        ));

        PredictThatReviewer::shouldNotReportAnyError($eventManager);

        $this->review($event);
    }

    function it_does_ignore_whitespace(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                '    A line ending with a colon:   ',
                '',
                '',
                "\t",
                '    .. code-block:: php'
            )),
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'The short syntax for PHP code (::) should be used here', 5);

        $this->review($event);
    }
}
