<?php

namespace spec\Docbot\Reviewer;

use Docbot\Event\RequestFileReview;
use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Promise\FileReviewEvent as PromiseThatEvent;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class TitleCaseSpec extends ReviewerBehaviour
{
    function it_find_words_that_should_be_capitialized(RequestFileReview $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveFile($event, Text::fromArray(array(
            'A wrong capitalized title of A section',
            '==========================',
        )));

        PredictThatReviewer::shouldReportError($eventManager, '(experimental) All words, except from closed-class words, have to be capitalized: "A Wrong Capitalized Title of a Section"', 1);

        $this->review($event);
    }
}
