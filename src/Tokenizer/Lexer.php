<?php

namespace Docbot\Tokenizer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Lexer
{
    /** @var array */
    private $lines;
    /** @var array */
    private $tokens;

    /**
     * @param string $markup reStructured Text markup
     *
     * @return array
     */
    public function tokenize($markup)
    {
        $this->lines = preg_split('/\R/', $markup);
        $this->tokens = [];

        do {
            $this->parse();
        } while (false !== next($this->lines));

        return $this->tokens;
    }

    /**
     * Parses the markup and creates a list of tokens.
     */
    private function parse()
    {
        $simpleNameRegex = '[A-Za-z0-9](?:[A-Za-z0-9]|[-_+:.][A-Za-z0-9])*';

        if ($this->isCurrentLineBlank()) {
            $this->tokens[] = Token::whitespace()->withValue($this->currentLine()."\n");

            return;
        }

        /* Directives
         *
         * +-------+-------------------------------+
         * | ".. " | directive type "::" directive |
         * +-------+ block                         |
         *         |                               |
         *         +-------------------------------+
         */
        if (preg_match('/^(\s*)(\.\.\s+('.$simpleNameRegex.')::)(\s*$|\s+.+$)/', $this->currentLine(), $matches)) {
            $this->tokens[] = Token::directiveMarker()->withValue($matches[2])->atOffset(strlen($matches[1]));
            $type = $matches[3];

            if (trim($matches[4])) {
                $this->tokens[] = Token::whitespace()->withValue(' ');
                $this->tokens[] = Token::directiveArgument()->withValue(substr($matches[4], 1));
            }

            $this->insertNewLineToken();

            $content = [];
            $startIndent = strlen($matches[1]);
            $indent = 0;

            while (
                $this->moveToNextLine()
                && ($this->isCurrentLineBlank()
                    || self::isIndentedHigher($this->currentLine(), $startIndent)
                )
            ) {
                if (preg_match('/^(\s+)(:'.$simpleNameRegex.':(?:\s.*)?)$/', $this->currentLine(), $matches)) {
                    $this->tokens[] = Token::directiveOption()->withValue($matches[2])->atOffset(strlen($matches[1]));
                    $this->insertNewLineToken();

                    continue;
                }

                if (!$this->isCurrentLineBlank() && 0 === $indent) {
                    $indent = self::getIndent($this->currentLine());
                }

                $content[] = $this->currentLine();
            }

            $this->moveToPrevLine();

            if ('code-block' === $type || 'index' === $type || 'toctree' === $type) {
                if ('' === trim(reset($content))) {
                    $this->insertNewLineToken();
                    array_shift($content);
                }

                $insertNewLine = false;
                if ('' === trim(end($content))) {
                    $insertNewLine = true;
                    array_pop($content);
                }

                $this->tokens[] = Token::raw()->withValue(implode("\n", $content));
                if ($insertNewLine) {
                    $this->insertNewLineToken();
                }

                if (null !== $this->getNextLine()) {
                    $this->insertNewLineToken();
                }
            } else {

                $lexer = new Lexer();

                $contentTokens = $lexer->tokenize(implode("\n", array_map(function ($l) use ($indent) {
                    return substr($l, $indent);
                }, $content)));

                $this->tokens = array_merge($this->tokens, array_map(function ($t) use ($indent) {
                    $t->atOffset($t->offset() + $indent);

                    return $t;
                }, $contentTokens));
            }

            return;
        }

        /* Literal Blocks
         *
         * +------------------------------+
         * | paragraph                    |
         * | (ends with "::")             |
         * +------------------------------+
         *    +---------------------------+
         *    | indented literal block    |
         *    +---------------------------+
         */
        if ('::' === substr($l = $this->getPrevNonWhitespaceLine(), -2)) {
            if (self::isIndentedHigher($this->currentLine(), $indent = self::getIndent($l))) {
                $startIndent = self::getIndent($this->currentLine());
                $value = [$this->currentLine()];

                while (
                    $this->moveToNextLine()
                    && ($this->isCurrentLineBlank()
                        || self::isIndentedEquallyOrHigher($this->currentLine(), $startIndent)
                    )
                ) {
                    $value[] = $this->currentLine();
                }

                if ('' === trim(end($value))) {
                    array_pop($value);
                    $this->moveToPrevLine();
                }

                $this->tokens[] = Token::indentedLiteralBlock()->withValue(implode("\n", $value));

                if (false !== $this->currentLine()) {
                    $this->insertNewLineToken();
                    $this->moveToPrevLine();
                }

                return;
            } elseif (preg_match('/\s{'.$indent.'}([!"#$%&\'()*+,-.\/:;<=>?@[\\]^_`{|}~])/', $this->currentLine(), $matches)) {
                $value = $this->currentLine();
                $regex = '/\s{'.$indent.'}'.preg_quote($matches[1]).'/';

                while ($this->moveToNextLine() && preg_match($regex, $this->currentLine())) {
                    $value .= "\n".$this->currentLine();
                }

                $this->tokens[] = Token::quotedLiteralBlock()->withValue($value);

                return;
            }
        }

        /* Bullet & Enumerated Lists
         *
         * +------+-----------------------+
         * | "- " | list item             |
         * +------| (body elements)+      |
         *        +-----------------------+
         *
         * +-------+----------------------+
         * | "1. " | list item            |
         * +-------| (body elements)+     |
         *         +----------------------+
         */
        if (
            // bullet lists
            ($bullet = preg_match('/^(\s*[*+-]\s+)/', $this->currentLine(), $matches))
            // enumerated lists
            || preg_match('/^(\s*(?:[0-9]+|[A-Z]|[IVXLCDM]{2,10})(?:\.|\))\s+)/i', $this->currentLine(), $matches)
        ) {
            $value = [$this->currentLine()];
            $indent = strlen($matches[1]);

            while (
                $this->moveToNextLine()
                && ($this->isCurrentLineBlank()
                    || self::isIndentedEquallyOrHigher($this->currentLine(), $indent)
                )
            ) {
                $value[] = $this->currentLine();
            }

            if ('' === trim(end($value))) {
                array_pop($value);
                $this->moveToPrevLine();
            }

            $this->tokens[] = Token::create($bullet ? Token::BULLET_LIST : Token::ENUMERATED_LIST)->withValue(implode("\n", $value));

            if (false !== $this->currentLine()) {
                $this->insertNewLineToken();
                $this->moveToPrevLine();
            }

            return;
        }

        /* Grid Tables
         */
        if (
            '+-' === substr($this->currentLine(), 0, 2)
            && $this->moveToNextLine()
            && '|' === substr($this->currentLine(), 0, 1)
            && $this->moveToPrevLine()
        ) {
            $value = $this->currentLine();

            while (
                $this->moveToNextLine()
                && ('+' === substr($this->currentLine(), 0, 1)
                    || '|' === substr($this->currentLine(), 0, 1)
                )
            ) {
                $value .= "\n".$this->currentLine();
            }

            $this->tokens[] = Token::gridTable()->withValue($value);

            if (null !== $this->getNextLine()) {
                $this->insertNewLineToken();
                $this->moveToPrevLine();
            }

            return;
        }

        /* Simple Tables
         */
        if (preg_match('/^[=\s]+$/', $this->currentLine())) {
            $value = $this->currentLine();
            $i = 0;

            while ($this->moveToNextLine()) {
                if (preg_match('/^[=\s]+$/', $this->currentLine())) {
                    $i++;
                }

                $value .= "\n".$this->currentLine();

                if ($i == 2) {
                    break;
                }
            }

            $this->tokens[] = Token::simpleTable()->withValue($value);

            if (null !== $this->getNextLine()) {
                $this->insertNewLineToken();
            }

            return;
        }

        /* Block Quotes
         *
         * +------------------------------+
         * | (current level of            |
         * | indentation)                 |
         * +------------------------------+
         *    +---------------------------+
         *    | block quote               |
         *    | (body elements)+          |
         *    |                           |
         *    | -- attribution text       |
         *    |    (optional)             |
         *    +---------------------------+
         */
        $indent = self::getIndent($this->getPrevNonWhitespaceLine());
        if (self::isIndentedHigher($this->currentLine(), $indent)) {
            $startIndent = self::getIndent($this->currentLine());
            $value = [$this->currentLine()];

            while (
                $this->moveToNextLine()
                && ($this->isCurrentLineBlank()
                    || self::isIndentedEquallyOrHigher($this->currentLine(), $startIndent)
                )
            ) {
                $value[] = $this->currentLine();

                if (
                    false !== strpos($this->currentLine(), '--')
                    || false !== strpos($this->currentLine(), '—')
                ) {
                    break;
                }
            }

            if ('' === trim(end($value))) {
                array_pop($value);
                $this->moveToPrevLine();
            }

            $this->tokens[] = Token::blockQuote()->withValue(implode("\n", $value))->atOffset($startIndent);

            if (null !== $this->getNextLine()) {
                $this->insertNewLineToken();
            }

            return;
        }

        /* Footnotes
         *
         * +-------+-------------------------+
         * | ".. " | "[" label "]" footnote  |
         * +-------+                         |
         *         | (body elements)+        |
         *         +-------------------------+
         */
        if (preg_match('/^(\s*)\.\.\s\[([0-9]+|#|\*|#'.$simpleNameRegex.')\]/', $this->currentLine(), $matches)) {
            $value = [$this->currentLine()];
            $indent = strlen($matches[1]) + 3;

            while (
                $this->moveToNextLine()
                && ($this->isCurrentLineBlank()
                    || self::isIndentedEquallyOrHigher($this->currentLine(), $indent)
                )
            ) {
                $value[] = $this->currentLine();
            }

            $this->moveToPrevLine();

            $this->tokens[] = Token::footnote()->withValue(implode("\n", $value));

            if (null !== $this->getNextLine()) {
                $this->insertNewLineToken();
            }

            return;
        }

        /* Hyperlink Targets
         *
         * +-------+----------------------+
         * | ".. " | "_" name ":" link    |
         * +-------+ block                |
         *         |                      |
         *         +----------------------+
         */
        if (preg_match('/^(\s*)\.\.\s_`?(?:'.$simpleNameRegex.'|[\w\\:\s]+)`?:/', $this->currentLine(), $matches)) {
            $value = $this->currentLine();
            $indent = strlen($matches[1]) + 3;

            while (
                $this->moveToNextLine()
                && self::isIndentedEqually($this->currentLine(), $indent)
            ) {
                $value .= "\n".$this->currentLine();
            }

            $this->moveToPrevLine();

            $this->tokens[] = Token::hyperlinkTarget()->withValue($value);

            if (null !== $this->getNextLine()) {
                $this->insertNewLineToken();
            }

            return;
        }

        /* Comments
         *
         * +-------+----------------------+
         * | ".. " | comment              |
         * +-------+ block                |
         *         |                      |
         *         +----------------------+
         */
        if (preg_match('/^\s*\.\.(?:\s|$)/', $this->currentLine())) {
            $value = [$this->currentLine()];
            $indent = self::getIndent($this->currentLine());

            if (
                $this->moveToNextLine()
                && ($this->isCurrentLineBlank()
                    || self::isIndentedHigher($this->currentLine(), $indent)
                )
            ) {
                $value[] = $this->currentLine();
                $indent = self::getIndent($this->currentLine());


                while (
                    $this->moveToNextLine()
                    && ($this->isCurrentLineBlank()
                        || self::isIndentedEquallyOrHigher($this->currentLine(), $indent)
                    )
                ) {
                    $value[] = $this->currentLine();
                }
            }

            $this->moveToPrevLine();

            $this->tokens[] = Token::comment()->withValue(implode("\n", $value));

            if (null !== $this->getNextLine()) {
                $this->insertNewLineToken();
            }

            return;
        }

        /* Section Titles
         *
         * +------------------------------------+
         * | headline                           |
         * +------------------------------------+
         * | [!"#$%&'()*+,-./:;<=>?@[\]^_`{|}~] |
         * +------------------------------------+
         */
        $indent = self::getIndent($this->currentLine());
        if (preg_match('/^\s{'.$indent.'}([!"#$%&\'()*+,-.\/:;<=>?@[\]^_`{|}\~])\1{2,}\s*$/', $this->getNextLine())) {
            $this->tokens[] = Token::headline()->withValue($this->currentLine());
            $this->insertNewLineToken();

            $this->moveToNextLine();
            $this->tokens[] = Token::headlineUnderline()->withValue($this->currentLine());

            if (null !== $this->getNextLine()) {
                $this->insertNewLineToken();
            }

            return;
        }

        /* Definition Lists
         */
        $indent = self::getIndent($this->currentLine());
        $nextLine = $this->getNextLine();
        if ('' !== trim($nextLine) && self::isIndentedHigher($nextLine, $indent)) {
            $value = [$this->currentLine()];
            $startIndent = self::getIndent($nextLine);

            while (
                $this->moveToNextLine()
                && ($this->isCurrentLineBlank()
                    || self::isIndentedEquallyOrHigher($this->currentLine(), $startIndent)
                )
            ) {
                $value[] = $this->currentLine();
            }

            $this->moveToPrevLine();

            if ('' === trim(end($value))) {
                array_pop($value);
                $this->moveToPrevLine();
            }

            $this->tokens[] = Token::definitionList()->withValue(implode("\n", $value))->atOffset($indent);

            if (null !== $this->getNextLine()) {
                $this->insertNewLineToken();
            }

            return;
        }

        /* Paragraphs
         *
         * +------------------------------+
         * | paragraph                    |
         * |                              |
         * +------------------------------+
         *
         * +------------------------------+
         * | paragraph                    |
         * |                              |
         * +------------------------------+
         */
        $value = $this->currentLine();
        $indent = strlen($value) - strlen(ltrim($value));
        while (
            $this->moveToNextLine()
            && self::isIndentedEqually($this->currentLine(), $indent)
        ) {
            $value .= "\n".$this->currentLine();
        }

        $this->tokens[] = Token::paragraph()->withValue($value);

        if (false !== $this->currentLine()) {
            $this->insertNewLineToken();
            $this->moveToPrevLine();
        }
    }

    private function moveToNextLine($includeNewLineToken = false)
    {
        if ($includeNewLineToken) {
            $this->insertNewLineToken();
        }

        return false !== next($this->lines);
    }

    private function moveToPrevLine()
    {
        return false !== prev($this->lines);
    }

    private function prevIfNotStartOfFile()
    {
        if (false !== $this->currentLine()) {
            prev($this->lines);
        }
    }

    private function insertNewLineToken()
    {
        $this->tokens[] = Token::whitespace()->withValue("\n");
    }

    private function currentLine()
    {
        return current($this->lines);
    }

    private function getPrevNonWhitespaceLine()
    {
        $lines = $this->lines;

        if ('' !== trim(prev($lines))) {
            return current($lines);
        }

        while (prev($lines) && '' === trim(current($lines)));

        // fixme: this shouldn't be the way forward...
        return '' !== trim(current($lines)) ? current($lines) : prev($lines);
    }

    private function getPrevLine()
    {
        if ($this->moveToPrevLine()) {
            $line = $this->currentLine();
            $this->moveToNextLine();

            return $line;
        }

        reset($this->lines);
    }

    private function getNextLine()
    {
        if ($this->moveToNextLine()) {
            $line = $this->currentLine();
            $this->moveToPrevLine();

            return $line;
        }

        end($this->lines);
    }

    private function prevIfLastLineIsBlank(&$lines, array &$value)
    {
        if ($this->isCurrentLineBlank()) {
            $this->prevIfNotStartOfFile($lines);
        }
    }

    private function isCurrentLineBlank()
    {
        return '' === trim($this->currentLine());
    }

    private static function isIndentedEqually($line, $level)
    {
        return self::isIndentedBetween($line, $level, $level);
    }

    private static function isIndentedEquallyOrHigher($line, $level)
    {
        return self::isIndentedBetween($line, $level);
    }

    private static function isIndentedHigher($line, $level)
    {
        return self::isIndentedBetween($line, $level + 1);
    }

    private static function isIndentedBetween($line, $start = 0, $end = null)
    {
        $repeat = $start.','.$end;
        if ($start === $end) {
            $repeat = $start;
        }

        return (bool) preg_match('/^\s{'.$repeat.'}\S/', $line);
    }

    private static function getIndent($line)
    {
        return strlen($line) - strlen(ltrim($line));
    }
}
