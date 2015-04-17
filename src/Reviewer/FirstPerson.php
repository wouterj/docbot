<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\File;

/**
 * A reviewer checking for first person usage.
 *
 *  * The first person SHOULD be avoided in the documentation.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class FirstPerson extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        // exception to the rule: The Quick Tour and Best Practices sections are allowed to use the first person.
        if ($file instanceof File && preg_match('/^(?:quick_tour|best_practices)/', $file->getFilename())) {
            return;
        }

        if (preg_match('/\b(I(?!\.)|we|let\'s)\b/i', $line)) {
            $this->reportError('The first person ("I", "we", "let\'s") should always be avoided');
        }
    }
}
