<?php

namespace Stoffer;

use Gnugat\Redaktilo\InvalidLineNumberException;
use Gnugat\Redaktilo\Text;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Editor
{
    public static function map(Text $text, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Second argument to Editor#map() has to be a valid callable, '.gettype($callback).' given.');
        }

        try {
            for ($i = 0; ; $i++) {
                //$text->setCurrentLineNumber($i);
                call_user_func($callback, $text->getLine($i), $i, $text);
            }
        } catch (InvalidLineNumberException $e) {
            // no worries, we just reached the end of the file
        }
    }
}
