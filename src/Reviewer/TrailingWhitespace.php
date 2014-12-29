<?php

namespace Stoffer\Reviewer;

use Stoffer\Editor;
use Zend\EventManager\Event;

class TrailingWhitespace extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        if (0 === strlen($line)) {
            return;
        }

        if (rtrim($line) !== $line) {
            $this->reportError(
                'There should be no trailing whitespace at the end of a line',
                $line,
                $file,
                $lineNumber + 1
            );
        }
    }
}
