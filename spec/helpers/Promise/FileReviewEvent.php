<?php

namespace spec\helpers\Promise;

use Gnugat\Redaktilo\Text;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class FileReviewEvent
{
    public static function willHaveFile($event, Text $file)
    {
        $event->getFile()->willReturn($file);
    }
}
