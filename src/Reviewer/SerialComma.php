<?php

namespace Docbot\Reviewer;

/**
 * A reviewer checking for serial comma usage.
 *
 *  * Serial comma's SHOULD be avoided.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class SerialComma extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/(\w*), (and)/', $line, $matches)) {
            $this->reportError(
                sprintf('Serial (Oxford) comma\'s should be avoided: "[...] %s %s [...]"', $matches[1], $matches[2]),
                $line,
                $file,
                $lineNumber + 1
            );
        }
    }
}
