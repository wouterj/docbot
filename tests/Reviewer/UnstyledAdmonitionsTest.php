<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\UnstyledAdmonitions as UnstyledAdmonitionsCheck;
use Gnugat\Redaktilo\Text;

class UnstyledAdmonitionsTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return UnstyledAdmonitionsCheck::class;
    }

    public function getExamples()
    {
        $message = 'The "%s" directive is not styled on symfony.com';
        
        return [
            [
                Text::fromString(<<<RST
.. warning::

    I am stoffing you!

.. caution::

    Yes, prefect.

.. danger::

    I may put a comment on each line of your PR
RST
                ),
                [
                    $this->getViolationProphet(sprintf($message, 'warning'), 1),
                    $this->getViolationProphet(sprintf($message, 'danger'), 10),
                ],
                'Usage of unstyled directives is not allowed.'
            ],
        ];
    }
}
