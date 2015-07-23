<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\SerialComma as SerialCommaCheck;
use Gnugat\Redaktilo\Text;

class SerialCommaTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return SerialCommaCheck::class;
    }

    public function getExamples()
    {
        $message = 'Serial (Oxford) comma\'s should be avoided: "[...] %s [...]"';

        return [
            [
                Text::fromString('One has an o, n, and e.'),
                [$this->getViolationProphet(sprintf($message, 'n and'), 1)],
                'A comma before and in a list is a serial comma'
            ],

            [
                Text::fromString('One is not two, three, or four.'),
                [$this->getViolationProphet(sprintf($message, 'three or'), 1)],
                'A comma before or in a list is a serial comma'
            ],

            /* fixme: implement this test case
            [
                Text::fromString('If you have an apple, and you are hungry, you can eat it.'),
                [],
                'Not all commas before conjunctions'
            ],
            */
        ];
    }
}
