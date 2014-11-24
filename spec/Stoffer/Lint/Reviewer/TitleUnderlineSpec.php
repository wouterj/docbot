<?php

namespace spec\Stoffer\Lint\Reviewer;

use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use spec\helpers\Promise\Event as PromiseThatEvent;

class TitleUnderlineSpec extends ReviewerBehaviour
{
    function it_finds_title_underlines_which_are_too_short(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'A title',
                '===',
            )),
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'The underline of a title should have exact the same length as the text of the title', 2);

        $this->review($event);
    }

    function it_finds_title_underlines_which_are_too_long(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'A title',
                '==========='
            ))
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'The underline of a title should have exact the same length as the text of the title', 2);

        $this->review($event);
    }
}
