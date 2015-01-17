<?php

namespace Stoffer\Reviewer;

use Stoffer\Editor;
use Zend\EventManager\Event;

class TitleCase extends Base
{
    static protected $closedClassWords = array(
        // conjunctions
        'and', 'or', 'but', 'if', 'while', 'unless', 'as', 'because', 'since', 'without',
        // determiners
        'the', 'a', 'an', 'any', 'those', 'which', 'other', 'your',
        // prepositions
        'to', 'from', 'at', 'with', 'of', 'in', 'between', 'inside', 'about', 'on',
        // pronouns
        'you', 'them', 'we', 'she', 'who', 'that', 'this',

        'when', 'how', 'versus', 'vs', 'let', 'is', 'be', 'for', 'each', 'not', 'out', 'based',
    );

    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/^([\~\!\"\#\$\%\&\'\(\)\*\+,-.\\\\\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}])\1{2,}/', $line)) {
            $titleText = trim($file->getLine($lineNumber - 1));

            $words = explode(' ', $titleText);
            if (count($words) === 1) {
                return;
            }

            $correctTitle = '';
            $nextShouldBeCapitialized = false;
            foreach ($words as $word) {
                $wordIsInClosedClass = in_array(strtolower($word), self::$closedClassWords);
                if (!$nextShouldBeCapitialized && $wordIsInClosedClass) {
                    // lowercase
                    $correctTitle .= strtolower($word[0]) . substr($word, 1);
                } else {
                    // capitialize
                    $correctTitle .= ucfirst($word);
                }

                if (in_array(substr($correctTitle, -1), array(':', '.'))) {
                    $nextShouldBeCapitialized = true;
                } else {
                    $nextShouldBeCapitialized = false;
                }

                $correctTitle .= ' ';
            }

            $correctTitle = trim(ucfirst($correctTitle));

            if ($correctTitle !== $titleText) {
                $this->reportError(
                    'All words, except from closed-class words, have to be capitalized: "'.$correctTitle.'" (experimental)',
                    $file->getLine($lineNumber - 1),
                    $file,
                    $lineNumber
                );
            }
        }
    }
}
