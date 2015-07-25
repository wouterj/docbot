<?php

namespace Docbot\Tokenizer;

/**
 * The token collection for reStructuredText markup.
 * 
 * @author Wouter J <wouter@wouterj.nl>
 */
class Tokens extends \SplFixedArray
{
    public static function fromArray(array $tokens)
    {
        $collection = new self(count($tokens));

        foreach ($tokens as $key => $value) {
            $collection[$key] = $value;
        }

        return $collection;
    }
    
    /**
     * Creates a token collection from markup.
     * 
     * @param string $markup
     * 
     * @return self
     */
    public static function fromMarkup($markup)
    {
        return self::fromArray(Lexer::tokenize($markup));
    }

    /**
     * @return Token
     */
    public function getNextNonWhitespace()
    {
        do {
            $this->next();
            
            if (!$this->valid()) {
                return;
            }
        } while ($this->current()->isGivenType(Token::WHITESPACE));
        
        return $this->current();
    }

    public function getPrevNonWhitespace()
    {
        $index = $this->key();
        
        do {
            $index--;
            
            if (!isset($this[$index])) {
                return;
            }
        } while (
            $this[$index]->isGivenType(Token::WHITESPACE)
        );
        
        $this->rewind();
        
        while ($this->key() !== $index) {
            $this->next();
        }

        return $this->current();
    }
    
    public function findGivenKind($type)
    {
        if ($this->current()->isGivenType($type)) {
            return $this->current();
        }
        
        foreach ($this as $token) {
            if ($token->isGivenType($type)) {
                return $token;
            }
        }
        
        return null;
    }
    
    public function getNextTokenOfKind()
    {
    }
    
    public function getPrevTokenOfKind()
    {
    }
    
    public function findSequence()
    {
    }
    
    public function insertAt()
    {
    }
    
    public function overrideAt()
    {
    }
    
    public function removeLeadingWhitespace()
    {
    }
    
    public function removeTrailingWhitespace()
    {
    }
    
    public function setMarkup()
    {
    }
}
