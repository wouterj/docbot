<?php

namespace Stoffer\Reviewer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class FaultyLiterals extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/\s`([^`]+)`[^_]/', $line, $matches)) {
            $this->reportError(
                'Found unrecognized usage of backticks. Did you mean to create a link (`'.$matches[1].'`_) or a literal (``'.$matches[1].'``)?',
                $line,
                $file,
                $lineNumber
            );
        }
    }
}
