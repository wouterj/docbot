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
        $index = $this->key();

        do {
            $index++;

            if (!isset($this[$index])) {
                return;
            }
        } while ($this[$index]->isGivenType(Token::WHITESPACE));

        return $index;
    }

    public function getPrevNonWhitespace()
    {
        $index = $this->key();

        do {
            $index--;

            if (!isset($this[$index])) {
                return;
            }
        } while ($this[$index]->isGivenType(Token::WHITESPACE));

        return $index;
    }

    public function prev()
    {
        $index = $this->key() - 1;

        if (!isset($this[$index])) {
            throw new \OutOfBoundsException('Index or range out of bounds.');
        }

        $this->rewind();

        while ($this->key() !== $index) {
            $this->next();
        }
    }

    /**
     * @param int[]|int $type
     *
     * @return Token|null
     */
    public function findGivenKind($type)
    {
        $this->rewind();

        foreach ($this as $token) {
            if ($token->isGivenType($type)) {
                return $token;
            }
        }

        return;
    }

    public function generateMarkup(Token $token = null)
    {
        if (null !== $token) {
            return $token->content();
        }

        $this->rewind();
        $markup = '';

        foreach ($this as $token) {
            $markup .= $this->generateMarkup($token)."\n";
        }

        return $markup ? substr($markup, 0, -1) : '';
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

    public function insertAt($index, Token $token)
    {
        $tokens = $this->toArray();

        array_splice($tokens, $index, 0, [$token]);

        $this->setSize(count($tokens));

        foreach ($tokens as $key => $t) {
            $this[$key] = $t;
        }
    }

    public function removeAt($index)
    {
        $tokens = $this->toArray();

        array_splice($tokens, $index, 1);

        $this->setSize(count($tokens));

        foreach ($tokens as $key => $t) {
            $this[$key] = $t;
        }
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
