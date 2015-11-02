<?php

namespace Docbot\Fixer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;
use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class TitleUnderlineFixer extends AbstractFixer
{
    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        /** @var Token[]|Tokens $tokens */
        $tokens = Tokens::fromMarkup($content);

        foreach ($tokens as $i => $token) {
            if (!$token->isGivenType(Token::HEADLINE_UNDERLINE)) {
                continue;
            }

            $title = $tokens[$tokens->getPrevNonWhitespace()]->value();
            $underline = $token->value();
            $newUnderline = str_repeat($underline[0], strlen($title));

            $token->withValue($newUnderline);
        }

        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
