<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\TrailingWhitespace as TrailingWhitespaceCheck;
use Gnugat\Redaktilo\Text;

class TrailingWhitespaceTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return TrailingWhitespaceCheck::class;
    }

    public function getExamples()
    {
        $message = 'There should be no trailing whitespace at the end of a line';
        
        return [
            [
                Text::fromString(<<<RST
a file with just normal text
     
followed by nasty trailing whitespace

and a simple empty line.
RST
                ),
                [$this->getViolationProphet($message, 2)],
                'Lines which contain only whitespace are not allowed'
            ],

            [
                Text::fromString(<<<RST
a line with whitespace after it     
a tab is also whitespace\t
RST
                ),
                [
                    $this->getViolationProphet($message, 1),
                    $this->getViolationProphet($message, 2),
                ],
                'Whitespace at the end of a line is not allowed'
            ],
        ];
    }
}
