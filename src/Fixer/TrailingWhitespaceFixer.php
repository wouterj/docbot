<?php

namespace Docbot\Fixer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;
use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class TrailingWhitespaceFixer extends AbstractFixer
{
    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        /** @var Token[]|Tokens $tokens */
        $tokens = Tokens::fromMarkup($content);

        // add new line feed at the end of the file if not present
        if (!$tokens->last()->isWhitespace()) {
            $tokens->insertAt($tokens->key() + 1, Token::whitespace()->withValue("\n"));
        } else {
            $tokens->prev();
        }

        // remove empty lines at the end of the file
        while ($tokens->valid() && $tokens->current()->isWhitespace()) {
            $tokens->removeAt($tokens->key());

            $tokens->prev();
        }

        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
        return 'Remove trailing whitespace at the end of lines.';
    }
}
