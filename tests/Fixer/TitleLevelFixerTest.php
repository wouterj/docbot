<?php

namespace Docbot\Test\Fixer;

use Docbot\Fixer\TitleLevelFixer;

class TitleLevelFixerTest extends FixerTestCase
{
    protected function createFixer()
    {
        return new TitleLevelFixer();
    }

    public function getExamples()
    {
        return [
            [
                <<<RST
Title Level 1
=============

Title level 2
~~~~~~~~~~~~~
RST
                ,
                <<<RST
Title Level 1
=============

Title level 2
-------------
RST
                ,
                'It fixes wrongly used underline level'
            ],

            [
                <<<RST
Title Level 1
+++++++++++++
RST
                ,
                <<<RST
Title Level 1
=============
RST
                ,
                'It fixes unused underline levels that are valid in reStructured Text'
            ],

            [
                <<<RST
Title level 1
=============

Title level 2
-------------

Title level 3
~~~~~~~~~~~~~

Title level 4
.............

Title level 2
-------------
RST
                ,
                <<<RST
Title level 1
=============

Title level 2
-------------

Title level 3
~~~~~~~~~~~~~

Title level 4
.............

Title level 2
-------------
RST
                ,
                'It accepts jumping multiple levels back'
            ],

            [
                <<<RST
Title level 3
~~~~~~~~~~~~~
RST
                ,
                <<<RST
Title level 3
~~~~~~~~~~~~~
RST
                ,
                'Inc files are allowed to start at deeper levels',
                __DIR__.'/../Fixtures/fixer.inc'
            ],
        ];
    }
}
