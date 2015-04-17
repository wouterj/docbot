<?php

namespace Docbot\Reviewer;

/**
 * A reviewer checking for trailing whitespaces.
 *
 *  * There SHOULDN'T be any trailing whitespaces.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class TrailingWhitespace extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        if (0 === strlen($line)) {
            return;
        }

        if (rtrim($line) !== $line) {
            $this->reportError('There should be no trailing whitespace at the end of a line');
        } elseif (preg_match('/[\w.]\s{2,}\w/', $line)) {
            $this->reportError('This line contains successive whitespaces');
        }
    }
}
