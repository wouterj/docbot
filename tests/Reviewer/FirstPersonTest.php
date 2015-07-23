<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\FirstPerson as FirstPersonCheck;
use Gnugat\Redaktilo\Text;

class FirstPersonTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return FirstPersonCheck::class;
    }

    public function getExamples()
    {
        $message = 'The first person ("I", "we", "let\'s") should always be avoided';

        return [
            [
                Text::fromString('I wrote this line!'),
                [$this->getViolationProphet($message, 1)],
                'The usage of I is not allowed'
            ],

            [
                Text::fromString(<<<RST
Let's make things worse...
and let's not improve it on this line.
RST
                ),
                [
                    $this->getViolationProphet($message, 1),
                    $this->getViolationProphet($message, 2),
                ],
                'The usage of let\'s is not allowed'
            ],

            [
                Text::fromString('We are writing this line together...'),
                [$this->getViolationProphet($message, 1)],
                'The usage of we is not allowed'
            ],

            /* fixme: implement this test case (refs #4)
            [
                Text::fromString('Usage of ``/[a-z]/i`` should be allowed'),
                [],
                'Any of the words inside a literal block should be allowed'
            ]
            */
        ];
    }
}
