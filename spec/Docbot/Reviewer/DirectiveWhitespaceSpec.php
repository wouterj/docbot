<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Promise\Event as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class DirectiveWhitespaceSpec extends ReviewerBehaviour
{
    function it_wants_an_empty_line_after_admonitions(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                '.. note::',
                '    The contents of the note.',
                '',
                '.. code-block::',
                '',
                '    // this is correct',
                '',
                'Hello::',
                '    // this is not',
            )),
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'There should be an empty line between the body and the start of a directive (except from versionadded directives)', 2);
        PredictThatReviewer::shouldReportError($eventManager, 'There should be an empty line between the body and the start of a directive (except from versionadded directives)', 9);

        $this->review($event);
    }

    function it_wants_the_body_direct_after_an_versionadded_directive(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                '.. versionadded:: 2.3',
                '    The feature X was introduced in XX.',
            )),
        ));

        PredictThatReviewer::shouldNotReportAnyError($eventManager);

        $this->review($event);
    }

    function it_errors_when_the_versionadded_body_is_not_directly_after_start(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                '.. versionadded:: 2.3',
                '',
                '    The feature X was introduced in XX.',
            ))
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'There should be no empty line between the start of a versionadded directive and the body', 3);

        $this->review($event);
    }
}
