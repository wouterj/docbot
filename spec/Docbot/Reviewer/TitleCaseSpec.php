<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Promise\Event as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class TitleCaseSpec extends ReviewerBehaviour
{
    function it_find_words_that_should_be_capitialized(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'A wrong capitalized title of A section',
                '==========================',
            ))
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'All words, except from closed-class words, have to be capitalized: "A Wrong Capitalized Title of a Section"', 1);

        $this->review($event);
    }
}
