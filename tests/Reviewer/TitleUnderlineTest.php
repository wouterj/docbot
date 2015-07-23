<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\TitleUnderline as TitleUnderlineCheck;
use Gnugat\Redaktilo\Text;

class TitleUnderlineTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return TitleUnderlineCheck::class;
    }

    public function getExamples()
    {
        $message = 'The underline of a title should have exact the same length as the text of the title';

        return [
            [
                Text::fromString(<<<RST
A title
====
RST
                ),
                [$this->getViolationProphet($message, 2)],
                'Underlines that are too short are invalid'
            ],

            [
                Text::fromString(<<<RST
A title
===========
RST
                ),
                [$this->getViolationProphet($message, 2)],
                'Underlines that are too long are invalid'
            ],
        ];
    }
}
