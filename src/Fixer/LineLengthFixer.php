<?php

namespace Docbot\Fixer;

use Docbot\Tokenizer\Token;
use Docbot\Tokenizer\Tokens;
use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class LineLengthFixer extends AbstractFixer
{
    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        $tokens = Tokens::fromMarkup($content);

        foreach ($tokens as $token) {
            $this->fixToken($token);
        }

        return $tokens->generateMarkup();
    }

    private function fixToken(Token $token)
    {
        if ($token->isCompound()) {
            foreach ($token->subTokens() as $subToken) {
                $this->fixToken($subToken);
            }

            return;
        }

        if ($token->isTable() || $token->isWhitespace()) {
            return;
        }

        if ($token->isLiteralBlock() /* && is_code_block_directive */) {
            // todo: 85 character limit for code blocks

            return;
        }

        $token->withValue($this->fixLineLength($token->value(), $token->offset()));
    }

    private function fixLineLength($str, $offset = 0)
    {
        $maxLength = 72 - $offset;

        $lines = explode("\n", $str);
        $fixedLines = [];
        $remainerOfPrevLine = '';

        // exception: definition lines do not have a limit
        if (1 === count($lines) && '**type**' === substr($lines[0], 0, 8)) {
            return $str;
        }

        foreach ($lines as $line) {
            $line = ($remainerOfPrevLine ? $remainerOfPrevLine.' ' : '').$line;

            if (strlen($line) > $maxLength) {
                $remainer = substr($line, $maxLength);

                if (false !== ($newWordPos = strpos($remainer, ' '))) {
                    $fixedLines[] = substr($line, 0, $maxLength + $newWordPos);
                    $remainerOfPrevLine = trim(substr($remainer, $newWordPos + 1));
                } else {
                    $fixedLines[] = $line;
                }
            } else {
                $fixedLines[] = $line;
            }
        }

        if ('' !== $remainerOfPrevLine) {
            $fixedLines[] = $this->fixLineLength($remainerOfPrevLine);
        }

        return implode("\n", $fixedLines);
    }

    /** @inheritDoc */
    public function getDescription()
    {
    }
}
