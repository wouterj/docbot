<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

/**
 * A reviewer checking for long PHP syntax, where short should be used.
 *
 *  * The short syntax (::) SHOULD be used instead of the long syntax (.. code-block:: php).
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ShortPhpSyntax extends Base
{
    public function reviewLine($line, $lineNumber, Text $file)
    {
        if ('.. code-block:: php' === trim($line)) {
            $lineBeforeNumber = $lineNumber;
            while (true) {
                $lineBefore = $file->getLine(--$lineBeforeNumber);

                if (trim($lineBefore) !== '') {
                    break;
                }
            }

            if (preg_match('/:$/', rtrim($lineBefore))) {
                $this->addError('The short syntax for PHP code (::) should be used here');
            }
        }
    }
}
