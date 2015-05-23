<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

/**
 * A reviewer checking for trailing whitespaces.
 *
 *  * There SHOULDN'T be any trailing whitespaces.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class TrailingWhitespace extends Base
{
    public function reviewLine($line, $lineNumber, Text $file)
    {
        if (0 === strlen($line)) {
            return;
        }

        if (rtrim($line) !== $line) {
            $this->addError('There should be no trailing whitespace at the end of a line');
        } elseif (preg_match('/[\w.]\s{2,}\w/', $line) && !preg_match('/^[\w=]+$/', $file->getLine($line + 1))) {
            $this->addError('This line contains successive whitespaces');
        }
    }
}
