<?php

namespace Docbot\Test\Tokenizer;

use Docbot\Tokenizer\Lexer;
use Docbot\Tokenizer\Token;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    private $lexer;

    protected function setUp()
    {
        $this->lexer = new Lexer();
    }

    public function testHeadlines()
    {
        $tokens = $this->lexer->tokenize(<<<RST
Hello
=====

Some Section
----------------
Paragraph of text.

Other section
~~~~~~~~

Other text.
RST
        );

        $this->assertTokens([
            ['HEADLINE', 'Hello'],
            ['WHITESPACE', "\n"],
            ['HEADLINE_UNDERLINE', '====='],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['HEADLINE', 'Some Section'],
            ['WHITESPACE', "\n"],
            ['HEADLINE_UNDERLINE', '----------------'],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'Paragraph of text.'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['HEADLINE', 'Other section'],
            ['WHITESPACE', "\n"],
            ['HEADLINE_UNDERLINE', '~~~~~~~~'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'Other text.'],
        ], $tokens);
    }

    public function testDirective()
    {
        $tokens = $this->lexer->tokenize(<<<RST
.. figure:: larch.png
    :scale: 50

    The larch. Wikipedia says.

No longer in directive.
RST
        );

        $this->assertTokens([
            ['DIRECTIVE_MARKER', '.. figure::'],
            ['WHITESPACE', ' '],
            ['DIRECTIVE_ARGUMENT', 'larch.png'],
            ['WHITESPACE', "\n"],
            ['DIRECTIVE_OPTION', ':scale: 50', 4],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'The larch. Wikipedia says.', 4],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['DIRECTIVE_END', null],
            ['PARAGRAPH', 'No longer in directive.'],
        ], $tokens);
    }

    public function testDirectiveWithoutSpacing()
    {
        $tokens = $this->lexer->tokenize(<<<RST
.. versionadded:: 2.5
    A cool new feature.
RST
        );

        $this->assertTokens([
            ['DIRECTIVE_MARKER', '.. versionadded::'],
            ['WHITESPACE', ' '],
            ['DIRECTIVE_ARGUMENT', '2.5'],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'A cool new feature.', 4],
            ['DIRECTIVE_END', null],
        ], $tokens);
    }

    public function testNestedDirective()
    {
        $tokens = $this->lexer->tokenize(<<<RST
.. configuration-block::

    .. code-block:: php

        echo 'Hello';

    .. code-block:: ruby

        puts 'Hello'
RST
        );

        $this->assertTokens([
            ['DIRECTIVE_MARKER', '.. configuration-block::'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['DIRECTIVE_MARKER', '.. code-block::', 4],
            ['WHITESPACE', ' '],
            ['DIRECTIVE_ARGUMENT', 'php'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'echo \'Hello\';', 8],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['DIRECTIVE_END', null],
            ['DIRECTIVE_MARKER', '.. code-block::', 4],
            ['WHITESPACE', ' '],
            ['DIRECTIVE_ARGUMENT', 'ruby'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'puts \'Hello\'', 8],
            ['DIRECTIVE_END', null],
            ['DIRECTIVE_END', null],
        ], $tokens);
    }

    public function testParagraph()
    {
        $tokens = $this->lexer->tokenize(<<<RST
Let's write a novel.
This is the same paragraph.

And this a new one!
RST
        );

        $this->assertTokens([
            ['PARAGRAPH', "Let's write a novel.\nThis is the same paragraph."],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'And this a new one!'],
        ], $tokens);
    }

    public function testBulletList()
    {
        $tokens = $this->lexer->tokenize(<<<RST
- List item 1

- List item 2
- List item 3
  with multi-line text
- List item 4

      With a Block Quote
RST
        );

        $this->assertTokens([
            ['BULLET_LIST', '- List item 1'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['BULLET_LIST', '- List item 2'],
            ['WHITESPACE', "\n"],
            ['BULLET_LIST', "- List item 3\n  with multi-line text"],
            ['WHITESPACE', "\n"],
            ['BULLET_LIST', "- List item 4\n\n      With a Block Quote"],
        ], $tokens);
    }

    public function testEnumeratedList()
    {
        $tokens = $this->lexer->tokenize(<<<RST
1. Enumerated List
9999. Enumerated List
      with multi-line
A) Enumerated List

MCMLIV. Enumerated List

AA. normal paragraph
RST
        );

        $this->assertTokens([
            ['ENUMERATED_LIST', '1. Enumerated List'],
            ['WHITESPACE', "\n"],
            ['ENUMERATED_LIST', "9999. Enumerated List\n      with multi-line"],
            ['WHITESPACE', "\n"],
            ['ENUMERATED_LIST', 'A) Enumerated List'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['ENUMERATED_LIST', 'MCMLIV. Enumerated List'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'AA. normal paragraph'],
        ], $tokens);
    }

    public function testDefinitionList()
    {
        $tokens = $this->lexer->tokenize(<<<RST
term 1
    Definition 1.

term 2 : classifier
    Definition 2.
RST
        );

        $this->assertTokens([
            ['DEFINITION_LIST', "term 1\n    Definition 1."],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['DEFINITION_LIST', "term 2 : classifier\n    Definition 2."],
        ], $tokens);
    }

    public function testIndentedLiteralBlock()
    {
        $tokens = $this->lexer->tokenize(<<<RST
This is a code example::

    puts 'Hello!'

::

    # only say good morning if it's morning

    puts 'Good morning' if morning
RST
        );

        $this->assertTokens([
            ['PARAGRAPH', 'This is a code example::'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['INDENTED_LITERAL_BLOCK', '    puts \'Hello!\''],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', '::'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['INDENTED_LITERAL_BLOCK', "    # only say good morning if it's morning\n\n    puts 'Good morning' if morning"],
        ], $tokens);
    }

    public function testQuotedLiteralBlock()
    {
        $tokens = $this->lexer->tokenize(<<<RST
And some quoted block. ::

> Hello world!
>> Something else
> Yeah.
RST
        );

        $this->assertTokens([
            ['PARAGRAPH', 'And some quoted block. ::'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['QUOTED_LITERAL_BLOCK', "> Hello world!\n>> Something else\n> Yeah."],
        ], $tokens);
    }

    public function testBlockQuote()
    {
        $tokens = $this->lexer->tokenize(<<<RST
Now comes a block quote.

    "It is my business to know things.  That is my trade."

    -- Sherlock Holmes

Another one.

    Block quote 2
RST
        );

        $this->assertTokens([
            ['PARAGRAPH', 'Now comes a block quote.'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['BLOCK_QUOTE', "    \"It is my business to know things.  That is my trade.\"\n\n    -- Sherlock Holmes"],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'Another one.'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['BLOCK_QUOTE', '    Block quote 2'],
        ], $tokens);
    }

    public function testGridTable()
    {
        $table = <<<RST
+------------------------+------------+----------+----------+
| Header row, column 1   | Header 2   | Header 3 | Header 4 |
| (header rows optional) |            |          |          |
+========================+============+==========+==========+
| body row 1, column 1   | column 2   | column 3 | column 4 |
+------------------------+------------+----------+----------+
| body row 2             | Cells may span columns.          |
+------------------------+------------+---------------------+
| body row 3             | Cells may  | - Table cells       |
+------------------------+ span rows. | - contain           |
| body row 4             |            | - body elements.    |
+------------------------+------------+---------------------+
RST;
        $tokens = $this->lexer->tokenize($table."\n\nSome text.");

        $this->assertTokens([
            ['GRID_TABLE', $table],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'Some text.'],
        ], $tokens);
    }

    public function testSimpleTable()
    {
        $table = <<<RST
=====  =====  =======
  A      B    A and B
=====  =====  =======
False  False  False
True   False  False
False  True   False
True   True   True
=====  =====  =======
RST;
        $tokens = $this->lexer->tokenize($table);

        $this->assertTokens([
            ['SIMPLE_TABLE', $table],
        ], $tokens);
    }

    public function testHyperlinkTarget()
    {
        $tokens = $this->lexer->tokenize(<<<RST
.. _`other_target`:

Some paragraph

.. _a-target:

.. _internal link: foo_
.. _`external: `:
   http://symfony.com/
RST
        );

        $this->assertTokens([
            ['HYPERLINK_TARGET', '.. _`other_target`:'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['PARAGRAPH', 'Some paragraph'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['HYPERLINK_TARGET', '.. _a-target:'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['HYPERLINK_TARGET', '.. _internal link: foo_'],
            ['WHITESPACE', "\n"],
            ['HYPERLINK_TARGET', ".. _`external: `:\n   http://symfony.com/"],
        ], $tokens);

    }

    public function testFootnote()
    {
        $tokens = $this->lexer->tokenize(<<<RST
Some footnote [1]_.

.. [1] The text
.. [#] And some other text
.. [#note]
   One starting below

   with multiple lines!
RST
        );

        $this->assertTokens([
            ['PARAGRAPH', 'Some footnote [1]_.'],
            ['WHITESPACE', "\n"],
            ['WHITESPACE', "\n"],
            ['FOOTNOTE', '.. [1] The text'],
            ['WHITESPACE', "\n"],
            ['FOOTNOTE', '.. [#] And some other text'],
            ['WHITESPACE', "\n"],
            ['FOOTNOTE', ".. [#note]\n   One starting below\n\n   with multiple lines!"],
        ], $tokens);
    }

    public function testComment()
    {
        $tokens = $this->lexer->tokenize(<<<RST
.. This is a comment
..
   _so: is this!
..
   [and] this!
..
   this:: too!
..
   |even| this:: !
RST
        );

        $this->assertTokens([
            ['COMMENT', '.. This is a comment'],
            ['WHITESPACE', "\n"],
            ['COMMENT', "..\n   _so: is this!"],
            ['WHITESPACE', "\n"],
            ['COMMENT', "..\n   [and] this!"],
            ['WHITESPACE', "\n"],
            ['COMMENT', "..\n   this:: too!"],
            ['WHITESPACE', "\n"],
            ['COMMENT', "..\n   |even| this:: !"],
        ], $tokens);
    }

    private function assertTokens($tokens, $actual)
    {
        foreach ($tokens as $i => $token) {
            $this->assertTokenType(current($actual), constant(Token::class.'::'.$token[0]), 'index: '.$i);
            $this->assertTokenEquals(current($actual), $token[1], 'index: '.$i);

            if (isset($token[2])) {
                $this->assertEquals($token[2], current($actual)->offset(), 'index: '.$i);
            }

            if (false === next($actual)) {
                break;
            }
        }

        $this->assertCount(count($tokens), $actual, 'There are more tokens than expected: '.implode(', ', array_map(function ($t) { return $t->type(true); }, $actual)));
    }

    private function assertTokenType(Token $token, $type, $extra = null)
    {
        $this->assertTrue($token->isGivenType($type), "\nActual type: ".$token->type(true).($extra ? ' ('.$extra.')' : ''));
    }

    private function assertTokenEquals(Token $token, $value, $extra = null)
    {
        $this->assertTrue($token->equals($value), "\nExpected value: ".json_encode($value)."\nActual value:   ".json_encode($token->value()).($extra ? ' ('.$extra.')' : ''));
    }
}
