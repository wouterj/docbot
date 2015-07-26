<?php

namespace Docbot\Test\Fixer;

use Docbot\Fixer\TitleUnderlineFixer;

class TitleUnderlineFixerTest extends FixerTestCase
{
    protected function createFixer()
    {
        return new TitleUnderlineFixer();
    }

    public function getExamples()
    {
        return [
            [
                <<<RST
A title
====
RST
                ,
                <<<RST
A title
=======
RST
                ,
                'Underlines that are too short are invalid'
            ],

            [
                <<<RST
A title
===========
RST
                ,
                <<<RST
A title
=======
RST
                ,
                'Underlines that are too long are invalid'
            ],
        ];
    }
}
