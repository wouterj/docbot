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
        
        foreach ($tokens as $token) {
            if (!$token->isGivenType(Token::SECTION_TITLE)) {
                continue;
            }
            
            list($title, $underline) = explode("\n", $token->content());
            $newUnderline = str_repeat($underline[0], strlen($title));
            
            $token->withValue($title."\n".$newUnderline);
        }
        
        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
