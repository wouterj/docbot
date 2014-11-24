<?php

namespace spec\helpers\Promise;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Event
{
    public static function willHaveParameters($event, array $params)
    {
        $paramObj = new \stdClass();

        foreach ($params as $name => $value) {
            $paramObj->$name = $value;
        }

        $event->getParams()->willReturn($paramObj);
    }
} 