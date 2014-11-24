<?php

namespace spec\helpers\Prediction;

use Prophecy\Argument;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Reviewer
{
    public static function shouldReportError($eventManager, $message, $lineNumber)
    {
        $eventManager->trigger('error_reported', 'reviewer', Argument::that(function ($event) use ($message, $lineNumber) {
            return $event->message === $message && $event->lineNumber === $lineNumber;
        }))->shouldBeCalled();
    }

    public static function shouldNotReportAnyError($eventManager)
    {
        $eventManager->trigger('error_reported', 'reviewer', Argument::any())->shouldNotBeCalled();
    }
} 