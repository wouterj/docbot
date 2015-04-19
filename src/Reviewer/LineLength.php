<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

/**
 * A reviewer checking the line length.
 *
 *  * There SHOULDN'T be a new word after the 72 character;
 *  * There SHOULDN'T be code after the 85 character.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class LineLength extends Base
{
    private $inCodeBlock = false;

    public function reviewLine($line, $lineNumber, Text $file) {
        if (false !== strpos($line, '.. code-block::') || ('..' !== substr($line, 0, 2) && '::' === substr(rtrim($line), -2))) {
            $this->inCodeBlock = strlen($line) - strlen(ltrim($line)) + 4;

            return;
        }

        if (false !== $this->inCodeBlock) {
            if (strlen(trim($line)) === 0) {
                return;
            }

            if (preg_match('/^\s{'.$this->inCodeBlock.'}/', $line)) {
                if (strlen(trim($line)) > 85) {
                    $this->addError('In order to avoid horizontal scrollbars, you should wrap the code on a 85 character limit');
                }

                return;
            }

            $this->inCodeBlock = false;
        }

        if (strlen(rtrim($line)) < 72) {
            return;
        }

        if (false !== strpos(substr(rtrim($line), 71), ' ')) {
            $this->addError('A line should be wrapped after the first word that crosses the 72th character');
        }
    }
}
