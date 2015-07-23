<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\RefRoleSpacing as RefRoleSpacingCheck;
use Gnugat\Redaktilo\Text;

class RefRoleSpacingTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return RefRoleSpacingCheck::class;
    }

    public function getExamples()
    {
        $message = 'There should be a space between "%s" and "%s".';

        return [
            [
                Text::fromString(<<<RST
:ref:`a label<the_reference>`
:ref:`other label <correct_reference>`
RST
                ),
                [$this->getViolationProphet(sprintf($message, 'a label', '<the_reference>'), 1)],
                'Ref roles must have a space between label and reference',
            ],

            [
                Text::fromString(<<<RST
:doc:`a label<the_reference>`
:doc:`other label <correct_reference>`
RST
                ),
                [$this->getViolationProphet(sprintf($message, 'a label', '<the_reference>'), 1)],
                'Doc roles must have a space between label and reference',
            ],
        ];
    }
}
