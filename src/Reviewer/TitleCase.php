<?php

namespace Stoffer\Reviewer;

use Stoffer\Editor;
use Zend\EventManager\Event;

class TitleCase extends Base
{
    static protected $closedClassWords = array('the', 'a', 'of', 'an', 'and', 'but', 'when', 'that', 'this', 'how', 'to', 'with', 'other', 'between', 'from', 'in', 'versus', 'you', 'we', 'let');

    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/^([\~\!\"\#\$\%\&\'\(\)\*\+,-.\\\\\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}])\1{2,}/', $line)) {
            $titleText = trim($file->getLine($lineNumber - 1));

            $words = explode(' ', $titleText);
            if (count($words) === 1) {
                return;
            }

            $correctTitle = '';
            foreach ($words as $word) {
                if (in_array(strtolower($word), self::$closedClassWords)) {
                    $correctTitle .= strtolower($word[0]) . substr($word, 1);
                } else {
                    $correctTitle .= ucfirst($word);
                }

                $correctTitle .= ' ';
            }

            $correctTitle = trim(ucfirst($correctTitle));

            if ($correctTitle !== $titleText) {
                $this->reportError(
                    'All words, except from closed-class words, have to be capitalized: "'.$correctTitle.'"',
                    $file->getLine($lineNumber - 1),
                    $file,
                    $lineNumber
                );
            }
        }
    }
}
