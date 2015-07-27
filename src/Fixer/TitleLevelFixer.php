<?php

namespace Docbot\Fixer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;
use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class TitleLevelFixer extends AbstractFixer
{
    private $levels = array(
        1 => '=',
        2 => '-',
        3 => '~',
        4 => '.',
        5 => '"',
        6 => '*',
    );
    
    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        /** @var Token[]|Tokens $tokens */
        $tokens = Tokens::fromMarkup($content);
        $currentLevel = 1;
        $firstTitle = true;
        $startLevelIsDetermined = false;
        
        foreach ($tokens as $token) {
            if (!$token->isGivenType(Token::SECTION_TITLE)) {
                continue;
            }
            
            $isFirstTitle = $firstTitle;
            $firstTitle = false;
            list($title, $underline) = explode("\n", $token->content());

            $level = array_search(ltrim($underline)[0], $this->levels);
            
            if (false === $level) {
                $character = $this->levels[$isFirstTitle ? 1 : ($currentLevel == 6 ? 6 : ++$currentLevel)];
                $underline = str_repeat($character, strlen($underline));
                
                $token->withValue($title."\n".$underline);
                
                continue;
            }

            // .inc files are allowed to start with a deeper level.
            $isIncludedFile = '.inc' === substr($file->getFilename(), -4);
            if ($isIncludedFile && !$startLevelIsDetermined) {
                $startLevelIsDetermined = true;
                $currentLevel = $level;
            }
            
            if ($level <= $currentLevel) {
                $currentLevel = $level;

                continue;
            }
            
            $underline = str_repeat($this->levels[++$currentLevel], strlen($underline));
                
            $token->withValue($title."\n".$underline);
        }
        
        return $tokens->generateMarkup();
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
