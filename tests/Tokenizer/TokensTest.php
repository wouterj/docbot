<?php

namespace WouterJ\Docbot\Test\Tokenizer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;

class TokensTest extends \PHPUnit_Framework_TestCase
{
    public function testGetNextNonWhitespace()
    {
        $tokens = Tokens::fromMarkup("\n\n\nHello!");
        
        $this->assertTrue($tokens->current()->isGivenType(Token::WHITESPACE), 'current token is whitespace');

        $this->assertTrue($tokens->getNextNonWhitespace()->equals('Hello!'), 'next non whitespace token is "Hello!"');
    }
    
    public function testGetNextNonWhitespaceNullIfEndOfFile()
    {
        $tokens = Tokens::fromMarkup('Hello!');
        
        $this->assertNull($tokens->getNextNonWhitespace());
    }
    
    public function testGetPrevNonWhitespace()
    {
        $tokens = Tokens::fromMarkup("Hello!\n\n\n");
        $tokens->next();
        $tokens->next();
        $tokens->next();

        $this->assertTrue($tokens->current()->isGivenType(Token::WHITESPACE), 'current token is whitespace');

        $this->assertTrue($tokens->getPrevNonWhitespace()->equals('Hello!'), 'next non whitespace token is "Hello!"');
    }

    public function testGetPrevNonWhitespaceNullIfStartOfFile()
    {
        $tokens = Tokens::fromMarkup('Hello!');

        $this->assertNull($tokens->getPrevNonWhitespace());
    }
    
    public function testFindGivenKind()
    {
        $tokens = Tokens::fromMarkup(<<<RST
.. note::

    Hello!
    
.. [1] Some footnote
RST
        );
        
        $this->assertTrue($tokens->findGivenKind(Token::FOOTNOTE)->isGivenType(Token::FOOTNOTE));
        $this->assertTrue($tokens->current()->equals('.. [1] Some footnote'));
    }
    
    public function testFindGivenKindNullIfNotFound()
    {
        $tokens = Tokens::fromMarkup("Symfony_\n\n.. _Symfony: http://symfony.com/");
        
        $this->assertNull($tokens->findGivenKind(Token::FOOTNOTE));
    }
}
