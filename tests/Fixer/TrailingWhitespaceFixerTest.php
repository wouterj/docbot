<?php

namespace Docbot\Test\Fixer;

use Docbot\Fixer\TrailingWhitespaceFixer;

class TrailingWhitespaceFixerTest extends FixerTestCase
{
    protected function createFixer()
    {
        return new TrailingWhitespaceFixer();
    }

    public function getExamples()
    {
        return [
            [
                <<<RST
a file with just normal text
    
followed by nasty trailing whitespace

and a simple empty line.
RST
                ,
                <<<RST
a file with just normal text

followed by nasty trailing whitespace

and a simple empty line.

RST
                ,
                'Lines which contain only whitespace are not allowed'
            ],

            [
                <<<RST
a line with whitespace after it   
a tab is also whitespace\t
RST
                ,
                <<<RST
a line with whitespace after it
a tab is also whitespace

RST
                ,
                'Whitespace at the end of a line is not allowed'
            ],
            
            [
                <<<RST
.. include:: /book/map.rst.inc

Cookbook
--------
RST
                ,
                <<<RST
.. include:: /book/map.rst.inc

Cookbook
--------

RST
                ,
            ],
            
            [
                <<<RST
Let's start with the simplest web application we can think of in PHP::

    // framework/index.php
    \$input = \$_GET['name'];

    printf('Hello %s', \$input);
RST
                ,
                <<<RST
Let's start with the simplest web application we can think of in PHP::

    // framework/index.php
    \$input = \$_GET['name'];

    printf('Hello %s', \$input);

RST
                ,
                'Blank lines are kept'
            ],
        ];
    }
}
