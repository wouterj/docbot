<?php

namespace spec\Stoffer\Lint\Reviewer;

use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use spec\helpers\Promise\Event as PromiseThatEvent;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;

class UnstyledAdmonitionsSpec extends ReviewerBehaviour
{
    function it_finds_unstyled_directives(Event $event, EventManagerInterface $eventManager)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                '.. warning::',
                '',
                '    I am stoffing you!',
                '',
                '.. danger::',
                '',
                '    I may put a comment on each line of your PR',
            )),
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'The "warning" directive is not styled on symfony.com', 1);
        PredictThatReviewer::shouldReportError($eventManager, 'The "danger" directive is not styled on symfony.com', 5);

        $this->review($event);
    }
}
