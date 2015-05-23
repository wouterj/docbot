<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

/**
 * A reviewer checking the title case (very experimental).
 *
 *  * All words in the title SHOULD be capitialized;
 *  * Except from closed-class words, which SHOULD be lowercased.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
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

    public function reviewLine($line, $lineNumber, Text $file)
    {
        if (preg_match('/^([\~\!\"\#\$\%\&\'\(\)\*\+,-.\\\\\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}])\1{3,}/', $line)) {
            if ($lineNumber === 0) {
                return;
            }

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

            if (ucfirst($correctTitle) !== $titleText) {
                // exception: field type references start with a lowercase word
                if (preg_match('/^[a-z]+ Field Type$/', trim($titleText))) {
                    return;
                }

                $this->addError(
                    '(experimental) All words, except from closed-class words, have to be capitalized: "%correct_title%"',
                    array('%correct_title%' => $correctTitle),
                    $lineNumber
                );
            }
        }
    }
}
