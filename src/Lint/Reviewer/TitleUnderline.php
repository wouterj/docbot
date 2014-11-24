<?php

namespace Stoffer\Lint\Reviewer;

use Stoffer\Lint\Editor;
use Zend\EventManager\Event;

class TitleUnderline extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/(^[\~\!\"\#\$\%\&\'\(\)\*\+,-.\\\\\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}])\1{2,}/', $line)) {
            $titleText = $file->getLine($lineNumber - 1);

            if (strlen(trim($titleText)) !== strlen(trim($line))) {
                $this->reportError(
                    'The underline of a title should have exact the same length as the text of the title',
                    $line,
                    $lineNumber + 1
                );
            }
        }
    }
}
