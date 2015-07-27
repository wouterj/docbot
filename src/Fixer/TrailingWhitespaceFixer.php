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

        foreach ($tokens as $token) {
            $this->fixToken($token);
        }
        
        
        if (!$tokens->last()->isWhitespace()) {
            $tokens->insertAt($tokens->key(), Token::whitespace());
        }
        
        return $tokens->generateMarkup();
    }

    private function fixToken(Token $token)
    {
        if ($token->isCompound()) {
            foreach ($token->subTokens() as $token) {
                $this->fixToken($token);
            }

            return;
        }
        
        if ($token->isGivenType(Token::DIRECTIVE_MARKER)) {
            return;
        }

        $token->withValue(preg_replace('/\h+$/m', '', $token->value()));
    }

    /** @inheritDoc */
    public function getDescription()
    {
        return 'Remove trailing whitespace at the end of lines.';
    }
}
