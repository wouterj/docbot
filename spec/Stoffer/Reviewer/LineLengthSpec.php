<?php

namespace spec\Stoffer\Reviewer;

use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use spec\helpers\Promise\Event as PromiseThatEvent;

class LineLengthSpec extends ReviewerBehaviour
{
    function let(EventManagerInterface $eventManager)
    {
        $this->setEventManager($eventManager);
    }

    function it_errors_when_a_new_word_is_after_the_72th_character(EventManagerInterface $eventManager, Event $event)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'A line that does not reach the limit',
                'A line that goes over the limit with a lot of words, as you can see in this sentence...',
            )),
        ));

        PredictThatReviewer::shouldReportError(
            $eventManager,
            'A line should be wrapped after the first word that crosses the 72th character',
            2
        );

        $this->review($event);
    }

    function it_does_not_error_when_a_long_word_crosses_the_limit(EventManagerInterface $eventManager, Event $event)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                'Tetaumatawhakatangihangakoauaotamateaurehaeaturipukapihimaungahoronukupokaiwhenuaakitanatahu'
            )),
        ));

        PredictThatReviewer::shouldNotReportAnyError($eventManager);

        $this->review($event);
    }

    function it_does_use_a_85_characters_limit_for_code(EventManagerInterface $eventManager, Event $event)
    {
        PromiseThatEvent::willHaveParameters($event, array(
            'file' => new Text(array(
                '.. code-block:: php',
                '',
                '    // a line that is around 80 characters long, so there should not be an error here',
                '    // but this should error, as it is longer than 80 characters long. Oh dear, what did I do?',
            )),
        ));

        PredictThatReviewer::shouldReportError($eventManager, 'In order to avoid horizontal scrollbars, you should wrap the code on a 85 character limit', 4);

        $this->review($event);
    }
}
