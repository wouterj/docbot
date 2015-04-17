<?php

namespace Docbot\Reviewer;

/**
 * A reviewer checking the title underline length.
 *
 *  * The title underline SHOULD be as long as the title itself.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class TitleUnderline extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/(^[\~\!\"\#\$\%\&\'\(\)\*\+,-.\\\\\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}])\1{3,}/', $line)) {
            $titleText = $file->getLine($lineNumber - 1);

            if (strlen(trim($titleText)) !== strlen(trim($line))) {
                $this->reportError('The underline of a title should have exact the same length as the text of the title');
            }
        }
    }
}
