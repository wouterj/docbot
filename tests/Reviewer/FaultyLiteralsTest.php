<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\FaultyLiterals as FaultyLiteralsCheck;
use Gnugat\Redaktilo\Text;

class FaultyLiteralsTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return FaultyLiteralsCheck::class;
    }

    public function getExamples()
    {
        return [
            [
                Text::fromString('This is probably some `wrong` backtick usage.'),
                [$this->getViolationProphet('Found unrecognized usage of backticks. Did you mean to create a link (`wrong`_) or a literal (``wrong``)?', 1)],
                'Single backticks that are not roles or references were probably meant as literal',
            ],

            [
                Text::fromString('This is some :ref:`correct` backtick usage.'),
                [],
                'Roles are correct',
            ],

            [
                Text::fromString('This is some `correct`_ backtick usage.'),
                [],
                'References are correct',
            ],
        ];
    }
}
