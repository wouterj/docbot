<?php

namespace Docbot\Fixer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;
use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class SerialCommaFixer extends AbstractFixer
{
    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        $tokens = Tokens::fromMarkup($content);

        foreach ($tokens as $token) {
            if ($token->isCode()) {
                continue;
            }

            $this->fixToken($token);
        }

        return $tokens->generateMarkup();
    }

    private function fixToken(Token $token)
    {
        $lines = explode("\n", $token->value());
        $fixedLines = [];
        $i = 0;
        foreach ($lines as $line) {
            if (false === ($pos = strpos($line, ', and')) && false === ($pos = strpos($line, ', or'))) {
                $fixedLines[] = $line;
                $i++;

                continue;
            }

            // only remove the comma if it's in a listing. Currently this
            // means: it has at least 2 comma's before in the previous and
            // current line.
            $searchLine = ($i >= 1 ? $fixedLines[$i - 1] : '').' '.substr($line, 0, $pos);
            if (substr_count($searchLine, ',') < 3) {
                $fixedLines[] = $line;
                $i++;

                continue;
            }

            $fixedLines[] = preg_replace('/,\s(and|or)/', ' $1', $line);
            $i++;
        }

        $token->withValue(implode("\n", $fixedLines));
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
