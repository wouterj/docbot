<?php

namespace Docbot\Test\Tokenizer;

use Docbot\Tokenizer\Lexer;
use Docbot\Tokenizer\Token;

class LexerTest extends \PHPUnit_Framework_TestCase
{
    public function testSection()
    {
        $tokens = Lexer::tokenize(<<<RST
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
        
        $this->assertCount(8, $tokens);
        
        $this->assertTokenType($tokens[0], Token::SECTION_TITLE);
        $this->assertTokenEquals($tokens[0], "Hello\n=====");
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::SECTION_TITLE);
        $this->assertTokenEquals($tokens[2], "Some Section\n----------------");
        $this->assertTokenType($tokens[3], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[3], 'Paragraph of text.');
        $this->assertTokenType($tokens[4], Token::WHITESPACE);
        $this->assertTokenType($tokens[5], Token::SECTION_TITLE);
        $this->assertTokenEquals($tokens[5], "Other section\n~~~~~~~~");
        $this->assertTokenType($tokens[6], Token::WHITESPACE);
        $this->assertTokenType($tokens[7], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[7], 'Other text.');
    }
    
    public function testDirective()
    {
        $tokens = Lexer::tokenize(<<<RST
.. figure:: larch.png
    :scale: 50

    The larch. Wikipedia says:

        Larches are conifers in the genus Larix, in the family Pinaceae.

No longer in directive.
RST
        );

        $this->assertCount(3, $tokens);

        $this->assertTokenType($tokens[0], Token::DIRECTIVE);
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::PARAGRAPH);

        $this->assertCount(5, $subTokens = $tokens[0]->subTokens());

        $this->assertTokenType($subTokens[0], Token::DIRECTIVE_MARKER);
        $this->assertTokenEquals($subTokens[0], '.. figure:: ');
        $this->assertTokenType($subTokens[1], Token::DIRECTIVE_ARGUMENT);
        $this->assertTokenEquals($subTokens[1], 'larch.png');
        $this->assertTokenType($subTokens[2], Token::DIRECTIVE_OPTION);
        $this->assertTokenEquals($subTokens[2], '    :scale: 50');
        $this->assertTokenType($subTokens[3], Token::WHITESPACE);

        $this->assertTokenType($subTokens[4], Token::DIRECTIVE_CONTENT);
        $contentToken = $subTokens[4];
        $this->assertCount(3, $contentSubTokens = $contentToken->subTokens());
        $this->assertTokenType($contentSubTokens[0], Token::PARAGRAPH);
        $this->assertTokenEquals($contentSubTokens[0], 'The larch. Wikipedia says:');
        $this->assertEquals(4, $contentSubTokens[0]->offset());
        $this->assertTokenType($contentSubTokens[1], Token::WHITESPACE);
        $this->assertTokenType($contentSubTokens[2], Token::BLOCK_QUOTE);
        $this->assertTokenEquals($contentSubTokens[2], '    Larches are conifers in the genus Larix, in the family Pinaceae.');
    }

    public function testParagraph()
    {
        $tokens = Lexer::tokenize(<<<RST
Let's write a novel.
This is the same paragraph.

And this a new one!
RST
        );

        $this->assertCount(3, $tokens);

        $this->assertTokenType($tokens[0], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[0], "Let's write a novel.\nThis is the same paragraph.");
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[2], 'And this a new one!');
    }

    public function testBulletList()
    {
        $tokens = Lexer::tokenize(<<<RST
- List item 1

- List item 2
- List item 3
  with multi-line text
RST
        );

        $this->assertCount(4, $tokens);

        $this->assertTokenType($tokens[0], Token::BULLET_LIST);
        $this->assertTokenEquals($tokens[0], '- List item 1');
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::BULLET_LIST);
        $this->assertTokenEquals($tokens[2], '- List item 2');
        $this->assertTokenType($tokens[3], Token::BULLET_LIST);
        $this->assertTokenEquals($tokens[3], "- List item 3\n  with multi-line text");
    }

    public function testEnumeratedList()
    {
        $tokens = Lexer::tokenize(<<<RST
1. Enumerated List
9999. Enumerated List
      with multi-line
A) Enumerated List

MCMLIV. Enumerated List

AA. normal paragraph
RST
        );

        $this->assertCount(7, $tokens);

        $this->assertTokenType($tokens[0], Token::ENUMERATED_LIST);
        $this->assertTokenEquals($tokens[0], '1. Enumerated List');
        $this->assertTokenType($tokens[1], Token::ENUMERATED_LIST);
        $this->assertTokenEquals($tokens[1], "9999. Enumerated List\n      with multi-line");
        $this->assertTokenType($tokens[2], Token::ENUMERATED_LIST);
        $this->assertTokenEquals($tokens[2], 'A) Enumerated List');
        $this->assertTokenType($tokens[3], Token::WHITESPACE);
        $this->assertTokenType($tokens[4], Token::ENUMERATED_LIST);
        $this->assertTokenEquals($tokens[4], 'MCMLIV. Enumerated List');
        $this->assertTokenType($tokens[5], Token::WHITESPACE);
        $this->assertTokenType($tokens[6], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[6], 'AA. normal paragraph');
    }

    public function testDefinitionList()
    {
        $this->markTestSkipped('Definition lists are not yet implemented');

        $tokens = Lexer::tokenize(<<<RST
term 1
    Definition 1.

term 2 : classifier
    Definition 2.
RST
        );

        $this->assertCount(3, $tokens);

        $this->assertTokenType($tokens[0], Token::DEFINITION_LIST);
        $this->assertTokenEquals($tokens[0], "term 1\n    Definition 1.");
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::DEFINITION_LIST);
        $this->assertTokenEquals($tokens[2], "term 2 : classifier\n    Definition 2.");
    }

    public function testIndentedLiteralBlock()
    {
        $tokens = Lexer::tokenize(<<<RST
This is a code example::

    echo 'Hello!';

::

    # only say good morning if it's morning

    puts 'Good morning' if morning
RST
        );

        $this->assertCount(7, $tokens);

        $this->assertTokenType($tokens[0], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[0], 'This is a code example::');
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::INDENTED_LITERAL_BLOCK);
        $this->assertTokenEquals($tokens[2], '    echo \'Hello!\';');
        $this->assertTokenType($tokens[3], Token::WHITESPACE);
        $this->assertTokenType($tokens[4], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[4], '::');
        $this->assertTokenType($tokens[5], Token::WHITESPACE);
        $this->assertTokenType($tokens[6], Token::INDENTED_LITERAL_BLOCK);
        $this->assertTokenEquals($tokens[6], "    # only say good morning if it's morning\n\n    puts 'Good morning' if morning");
    }

    public function testQuotedLiteralBlock()
    {
        $tokens = Lexer::tokenize(<<<RST
And some quoted block. ::

> Hello world!
>> Something else
> Yeah.
RST
        );

        $this->assertCount(3, $tokens);

        $this->assertTokenType($tokens[0], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[0], 'And some quoted block. ::');
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::QUOTED_LITERAL_BLOCK);
        $this->assertTokenEquals($tokens[2], "> Hello world!\n>> Something else\n> Yeah.");
    }

    public function testBlockQuote()
    {
        $tokens = Lexer::tokenize(<<<RST
Now comes a block quote.

    "It is my business to know things.  That is my trade."

    -- Sherlock Holmes

Another one.

    Block quote 2
RST
        );

        $this->assertCount(7, $tokens);

        $this->assertTokenType($tokens[0], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[0], 'Now comes a block quote.');
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::BLOCK_QUOTE);
        $this->assertTokenEquals($tokens[2], "    \"It is my business to know things.  That is my trade.\"\n\n    -- Sherlock Holmes");
        $this->assertTokenType($tokens[3], Token::WHITESPACE);
        $this->assertTokenType($tokens[4], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[4], 'Another one.');
        $this->assertTokenType($tokens[5], Token::WHITESPACE);
        $this->assertTokenType($tokens[6], Token::BLOCK_QUOTE);
        $this->assertTokenEquals($tokens[6], '    Block quote 2');
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
        $tokens = Lexer::tokenize($table);

        $this->assertCount(1, $tokens);

        $this->assertTokenType($tokens[0], Token::GRID_TABLE);
        $this->assertTokenEquals($tokens[0], $table);
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
        $tokens = Lexer::tokenize($table);

        $this->assertCount(1, $tokens);

        $this->assertTokenType($tokens[0], Token::SIMPLE_TABLE);
        $this->assertTokenEquals($tokens[0], $table);
    }

    public function testHyperlinkTarget()
    {
        $tokens = Lexer::tokenize(<<<RST
.. _`other_target`:

Some paragraph

.. _a-target:

.. _internal link: foo_
.. _`external: `:
   http://symfony.com/
RST
        );

        $this->assertCount(8, $tokens);

        $this->assertTokenType($tokens[0], Token::HYPERLINK_TARGET);
        $this->assertTokenEquals($tokens[0], '.. _`other_target`:');
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[2], 'Some paragraph');
        $this->assertTokenType($tokens[3], Token::WHITESPACE);
        $this->assertTokenType($tokens[4], Token::HYPERLINK_TARGET);
        $this->assertTokenEquals($tokens[4], '.. _a-target:');
        $this->assertTokenType($tokens[5], Token::WHITESPACE);
        $this->assertTokenType($tokens[6], Token::HYPERLINK_TARGET);
        $this->assertTokenEquals($tokens[6], '.. _internal link: foo_');
        $this->assertTokenType($tokens[7], Token::HYPERLINK_TARGET);
        $this->assertTokenEquals($tokens[7], ".. _`external: `:\n   http://symfony.com/");
    }

    public function testFootnote()
    {
        $tokens = Lexer::tokenize(<<<RST
Some footnote [1]_.

.. [1] The text
.. [#] And some other text
.. [#note]
   One starting below

   with multiple lines!
RST
        );

        $this->assertCount(5, $tokens);

        $this->assertTokenType($tokens[0], Token::PARAGRAPH);
        $this->assertTokenEquals($tokens[0], 'Some footnote [1]_.');
        $this->assertTokenType($tokens[1], Token::WHITESPACE);
        $this->assertTokenType($tokens[2], Token::FOOTNOTE);
        $this->assertTokenEquals($tokens[2], '.. [1] The text');
        $this->assertTokenType($tokens[3], Token::FOOTNOTE);
        $this->assertTokenEquals($tokens[3], '.. [#] And some other text');
        $this->assertTokenType($tokens[4], Token::FOOTNOTE);
        $this->assertTokenEquals($tokens[4], ".. [#note]\n   One starting below\n\n   with multiple lines!");
    }

    public function testComment()
    {
        $tokens = Lexer::tokenize(<<<RST
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

        $this->assertCount(5, $tokens);

        $this->assertTokenType($tokens[0], Token::COMMENT);
        $this->assertTokenEquals($tokens[0], '.. This is a comment');
        $this->assertTokenType($tokens[1], Token::COMMENT);
        $this->assertTokenEquals($tokens[1], "..\n   _so: is this!");
        $this->assertTokenType($tokens[2], Token::COMMENT);
        $this->assertTokenEquals($tokens[2], "..\n   [and] this!");
        $this->assertTokenType($tokens[3], Token::COMMENT);
        $this->assertTokenEquals($tokens[3], "..\n   this:: too!");
        $this->assertTokenType($tokens[4], Token::COMMENT);
        $this->assertTokenEquals($tokens[4], "..\n   |even| this:: !");
    }

    private function assertTokenType(Token $token, $type)
    {
        $this->assertTrue($token->isGivenType($type), $token->type(true));
    }

    private function assertTokenEquals(Token $token, $value)
    {
        $this->assertTrue($token->equals($value), "\nExpected value: ".json_encode($value)."\nActual value:   ".json_encode($token->value()));
    }
}
