<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

/**
 * A reviewer hinting for possibly wrong backtick usage.
 *
 * The reviewer catches occurences of single backticks: `something`. In this
 * case, it'll hint for a literal (``something``) or a reference (`something`_).
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class FaultyLiterals extends Base
{
    public function reviewLine($line, $lineNumber, Text $file)
    {
        if (preg_match('/\s`([^`]+)`[^_]/', $line, $matches)) {
            $this->addError(
                'Found unrecognized usage of backticks. Did you mean to create a link (`%value%`_) or a literal (``%value%``)?',
                array('%value%' => $matches[1])
            );
        }
    }
}
