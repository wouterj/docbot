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

        foreach ($tokens as $index => $token) {
            if (!$token->isGivenType(Token::DIRECTIVE)) {
                continue;
            }

            $subTokens = $token->subTokens();
            if (
                false === strpos($subTokens[0]->content(), '.. code-block::')
                || ($subTokens[1]->isGivenType(Token::DIRECTIVE_ARGUMENT) && !$subTokens[1]->equals('php'))
            ) {
                continue;
            }

            $prevToken = $tokens[$tokens->getPrevNonWhitespace()];

            if (!$prevToken->isGivenType(Token::PARAGRAPH)) {
                continue;
            }

            if (':' === substr(rtrim($prevToken->content()), -1)) {
                $prevToken->withValue(preg_replace('/(?<!:):\s*$/', '::', $prevToken->content()));

                $tokens->removeAt($index);
                $tokens->insertAt($index, Token::indentedLiteralBlock()->withValue($token->subTokens()->findGivenKind(Token::DIRECTIVE_CONTENT)->content()));
            }
        }

        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
