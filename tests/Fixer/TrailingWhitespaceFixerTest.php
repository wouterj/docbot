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
        ];
    }
}
