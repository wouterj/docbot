<?php

namespace Docbot\Fixer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;
use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class UnstyledDirectivesFixer extends AbstractFixer
{
    private $unstyledDirectivesReplacement = [
        'attention' => 'caution',
        'danger'    => 'caution',
        'error'     => 'caution',
        'hint'      => 'tip',
        'important' => 'caution',
        'warning'   => 'caution',
        'topic'     => 'note',
    ];

    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        /** @var Token[]|Tokens $tokens */
        $tokens = Tokens::fromMarkup($content);

        foreach ($tokens as $token) {
            if (!$token->isGivenType(Token::DIRECTIVE)) {
                continue;
            }

            $marker = $token->subTokens()[0];

            $marker->withValue(preg_replace_callback('/(\.\.\s)(.*?)(::)/', function ($matches) {
                if (!isset($this->unstyledDirectivesReplacement[$matches[2]])) {
                    return $matches[0];
                }

                return $matches[1].$this->unstyledDirectivesReplacement[$matches[2]].$matches[3];
            }, $marker->content()));
        }

        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
