<?php

namespace Docbot\Fixer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;
use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class DirectiveWhitespaceFixer extends AbstractFixer
{
    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        /** @var Token[]|Tokens $tokens */
        $tokens = Tokens::fromMarkup($content);

        foreach ($tokens as $index => $token) {
            if (!$token->isGivenType(Token::DIRECTIVE) && !$token->isLiteralBlock()) {
                continue;
            }

            if ($token->isLiteralBlock()) {
                if (!$tokens[$index - 1]->isWhitespace()) {
                    $tokens->insertAt($index, Token::whitespace());
                }

                continue;
            }

            $subTokens = $token->subTokens();
            preg_match('/\.\.\s(.+?)::/', $subTokens[0]->content(), $matches);
            $type = $matches[1];

            // move till the first token after the marker line + option lines
            while ($subTokens->current()->isGivenType([Token::DIRECTIVE_MARKER, Token::DIRECTIVE_ARGUMENT, Token::DIRECTIVE_OPTION])) {
                $subTokens->next();
            }

            if ('index' === $type || 'versionadded' === $type) {
                if ($subTokens->current()->isWhitespace()) {
                    $subTokens->removeAt($subTokens->key());
                }

                continue;
            }

            if ($subTokens->current()->isWhitespace()) {
                continue;
            }

            $subTokens->insertAt($subTokens->key(), Token::whitespace());
        }

        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
