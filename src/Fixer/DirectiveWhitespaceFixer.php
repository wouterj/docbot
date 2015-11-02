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
        $token = $tokens->current();
        $index = 0;

        do {
            if (!$token->isGivenType(Token::DIRECTIVE_MARKER) && !$token->isLiteralBlock()) {
                continue;
            }

            if ($token->isLiteralBlock()) {
                if (!$tokens[$index - 1]->isWhitespace()) {
                    $tokens->insertAt($index, Token::whitespace()->withValue("\n"));
                }
                if (!$tokens[$index - 2]->isWhitespace()) {
                    $tokens->insertAt($index, Token::whitespace()->withValue("\n"));
                }

                continue;
            }

            preg_match('/\.\.\s(.+?)::/', $token->value(), $matches);
            $type = $matches[1];

            // move till the first token after the marker line + option lines
            while ($tokens->current()->isGivenType([Token::DIRECTIVE_MARKER, Token::DIRECTIVE_ARGUMENT, Token::DIRECTIVE_OPTION, Token::WHITESPACE])) {
                $tokens->next();
            };

            $whitespaceLines = 0;

            if ('index' === $type || 'versionadded' === $type) {
                $i = $tokens->key() - 1;

                for (; $i >= 0; $i--) {
                    if (!$tokens[$i]->isWhitespace()) {
                        break;
                    }

                    if (++$whitespaceLines > 1) {
                        $tokens->removeAt($i);
                    }
                }
            } else {
                $i = $tokens->key() - 1;

                for (; $i >= 0; $i--) {
                    if (!$tokens[$i]->isWhitespace()) {
                        break;
                    }

                    if (++$whitespaceLines > 2) {
                        $tokens->removeAt($i);
                    }
                }

                if ($whitespaceLines < 2) {
                    $tokens->insertAt($tokens->key(), Token::whitespace()->withValue("\n"));
                }
            }
        } while (false !== ($token = $tokens->moveNext()) && ++$index);

        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
