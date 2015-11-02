<?php

namespace Docbot\Fixer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;
use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ShortPhpSyntaxFixer extends AbstractFixer
{
    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        /** @var Tokens|Token[] $tokens */
        $tokens = Tokens::fromMarkup($content);
        $token = $tokens->current();
        $index = 0;

        do {
            if (!$token->isGivenType(Token::DIRECTIVE_MARKER)) {
                continue;
            }

            if (false === strpos($token->value(), '.. code-block::')) {
                continue;
            }

            while ($tokens->current()->isGivenType([Token::DIRECTIVE_MARKER, Token::WHITESPACE]) && $tokens->moveNext());

            if (!$tokens->current()->isGivenType(Token::DIRECTIVE_ARGUMENT) || !$tokens->current()->equals('php')) {
                continue;
            }

            $prevToken = $tokens[$tokens->getPrevNonWhitespace($index)];

            if (!$prevToken->isGivenType(Token::PARAGRAPH)) {
                continue;
            }

            if (':' === substr(rtrim($prevToken->value()), -1)) {
                $prevToken->withValue(preg_replace('/(?<!:):\s*$/', '::', $prevToken->value()));

                for (
                    $i = $tokens->getPrevNonWhitespace($index) + 1;
                    isset($tokens[$i])
                    && $tokens[$i]->isGivenType([Token::WHITESPACE, Token::DIRECTIVE_ARGUMENT, Token::DIRECTIVE_MARKER]);
                    $i++
                ) {
                    $tokens->removeAt($i--);
                }

                $tokens->insertAt($i, Token::whitespace()->withValue("\n"));
                $tokens->insertAt($i, Token::whitespace()->withValue("\n"));
            }
        } while (false !== ($token = $tokens->moveNext()) && ++$index);

        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
