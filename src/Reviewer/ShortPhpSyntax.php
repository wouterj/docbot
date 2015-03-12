<?php

namespace Docbot\Reviewer;

class ShortPhpSyntax extends Base
{
    public function reviewLine($line, $lineNumber, $file)
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
                $this->reportError(
                    'The short syntax for PHP code (::) should be used here',
                    $line,
                    $file,
                    $lineNumber + 1
                );
            }
        }
    }
}
