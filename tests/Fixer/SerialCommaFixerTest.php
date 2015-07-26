<?php

namespace Docbot\Test\Fixer;

use Docbot\Fixer\SerialCommaFixer;

class SerialCommaFixerTest extends FixerTestCase
{
    protected function createFixer()
    {
        return new SerialCommaFixer();
    }

    public function getExamples()
    {
        return [
            [
                'One has an o, n, and e.',
                'One has an o, n and e.',
                'A comma before and in a list is a serial comma'
            ],

            [
                "One has an o,\nn, and e.",
                "One has an o,\nn and e.",
                'Lists can spant multiple lines'
            ],

            [
                'One is not two, three, or four.',
                'One is not two, three or four.',
                'A comma before or in a list is a serial comma'
            ],

            [
                'If you have an apple, and you are hungry, you can eat it.',
                'If you have an apple, and you are hungry, you can eat it.',
                'Not all commas before conjunctions'
            ],
        ];
    }
}
