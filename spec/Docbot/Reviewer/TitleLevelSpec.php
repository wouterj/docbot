<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use spec\helpers\Promise\Event as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;

class TitleLevelSpec extends ReviewerBehaviour
{
    function it_finds_wrong_level_characters(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'Title Level 1',
                '=============',
                '',
                'Title level 2',
                '~~~~~~~~~~~~~'
            ))
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'The "-" character should be used for a title level 2', 5);

        $this->review($event);
    }

    function it_finds_unused_underline_characters(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'Title Level 1',
                '+++++++++++++',
            ))
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'Only =, -, ~, . and " should be used as title underlines', 2);

        $this->review($event);
    }

    function it_accepts_jumping_levels_up(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new text(array(
                'Title level 1',
                '=============',
                '',
                'Title level 2',
                '-------------',
                '',
                'Title level 3',
                '~~~~~~~~~~~~~',
                '',
                'Title level 4',
                '.............',
                '',
                'Title level 2',
                '-------------',
            ))
        ));

        PredictThatReviewer::shouldNotReportAnyError($eventManager);

        $this->review($event);
    }
}
