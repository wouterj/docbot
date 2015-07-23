<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\TitleCase as TitleCaseCheck;
use Gnugat\Redaktilo\Text;

class TitleCaseTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return TitleCaseCheck::class;
    }

    public function getExamples()
    {
        $message = 'All words, except from closed-class words, have to be capitalized: "%s"';

        return [
            [
                Text::fromString(<<<RST
A wrong capitalized title of A section
==========================
RST
                ),
                [$this->getViolationProphet(sprintf($message, 'A Wrong Capitalized Title of a Section'), 1)],
                'All words should be capitalized, except from closed-class ones.'
            ],
        ];
    }
}
