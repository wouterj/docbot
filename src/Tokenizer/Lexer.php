<?php

namespace Docbot\Tokenizer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Lexer
{
    private static $cache = [];
    
    public static function clearCache()
    {
        self::$cache = [];
    }
    
    public static function tokenize($markup)
    {
        $hash = crc32($markup);
        
        if (isset(self::$cache[$hash])) {
            return self::$cache[$hash];
        }
        
        $markupLines = preg_split('/\R/', $markup);

        $tokens = [];
        do {
            $tokens[] = self::getTokens(current($markupLines), $markupLines);
        } while (false !== next($markupLines));

        return $tokens;
    }

    private static function getTokens($line, &$lines)
    {
        $simpleNameRegex = '[A-Za-z0-9](?:[A-Za-z0-9]|[-_+:.][A-Za-z0-9])*';

        if (self::isBlank($line)) {
            return Token::whitespace()->withValue($line);
        }

        /* Directives
         *
         * +-------+-------------------------------+
         * | ".. " | directive type "::" directive |
         * +-------+ block                         |
         *         |                               |
         *         +-------------------------------+
         */
        if (preg_match('/^(\s*)\.\.\s+'.$simpleNameRegex.'::(\s+|$)/', $line, $matches)) {
            $subTokens = [Token::directiveMarker()->withValue($matches[0])];

            if (strlen($line) > strlen($matches[0])) {
                $subTokens[] = Token::directiveArgument()->withValue(substr($line, strlen($matches[0])));
            }

            $value = [];
            $startIndent = strlen($matches[1]);
            $first = true;

            while (
                self::moveToNextLine($lines)
                && (self::isBlank(current($lines))
                    || self::isIndentedHigher(current($lines), $startIndent)
                )
            ) {
                if (preg_match('/^\s+:'.$simpleNameRegex.':(?:\s.*)?$/', current($lines), $matches)) {
                    $subTokens[] = Token::directiveOption()->withValue($matches[0]);

                    continue;
                }

                if ($first && self::isBlank(current($lines))) {
                    $subTokens[] = Token::whitespace()->withValue(current($lines));
                    $first = false;

                    continue;
                }
                $first = false;

                $value[] = current($lines);
            }

            self::prevIfLastLineIsBlank($lines, $value);

            prev($lines);

            if ($value) {
                $indent = self::getIndent($value[0]);
                $value = implode("\n", array_map(function ($v) use ($indent) { return substr($v, $indent); }, ($value)));
                $contentToken = Token::directiveContent();
                
                if (false === strpos($subTokens[0]->value(), '.. code-block::')) {
                    $contentSubTokens = [];
                    foreach (self::tokenize($value) as $t) {
                        $contentSubTokens[] = $t->atOffset($t->offset() + $indent);
                    }
                    
                    $subTokens[] = $contentToken->withSubTokens($contentSubTokens);
                } else {
                    $subTokens[] = $contentToken->atOffset($indent)->withValue($value);
                }
            }

            return Token::directive()->withSubTokens($subTokens);
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
        if ('::' === substr($l = self::getPrevNonWhitespaceLine($lines), -2)) {
            $indent = self::getIndent($l);

            if (self::isIndentedHigher($line, $indent)) {
                $value = [$line];

                while (
                    self::moveToNextLine($lines)
                    && (self::isBlank(current($lines))
                        || self::isIndentedHigher(current($lines), $indent)
                    )
                ) {
                    $value[] = current($lines);
                }

                self::prevIfLastLineIsBlank($lines, $value);

                prev($lines);
                
                $indent = self::getIndent($line);
                $value = array_map(function ($v) use ($indent) {
                    return substr($v, $indent);
                }, $value);

                return Token::create(Token::INDENTED_LITERAL_BLOCK)->atOffset($indent)->withValue(implode("\n", $value));
            } elseif (preg_match('/\s{'.$indent.'}([!"#$%&\'()*+,-.\/:;<=>?@[\\]^_`{|}~])/', $line, $matches)) {
                $value = $line;
                while (
                    self::moveToNextLine($lines)
                    && preg_match('/\s{'.$indent.'}'.preg_quote($matches[1]).'/', current($lines))
                ) {
                    $value .= "\n".current($lines);
                }

                return Token::quotedLiteralBlock()->withValue($value);
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
            ($bullet = preg_match('/^(\s*[*+-]\s+)/', $line, $matches))
            // enumerated lists
            || preg_match('/^(\s*(?:[0-9]+|[A-Z]|[IVXLCDM]{2,10})(?:\.|\))\s+)/i', $line, $matches)
        ) {
            $value = [$line];
            $indent = strlen($matches[1]);

            while (
                self::moveToNextLine($lines)
                && (self::isBlank(current($lines))
                    || self::isIndentedEquallyOrHigher(current($lines), $indent)
                )
            ) {
                $value[] = current($lines);
            }
            
            self::prevIfLastLineIsBlank($lines, $value);

            self::prevIfNotStartOfFile($lines);

            return Token::create($bullet ? Token::BULLET_LIST : Token::ENUMERATED_LIST)->withValue(implode("\n", $value));
        }

        /* Grid Tables
         */
        if (
            '+-' === substr($line, 0, 2)
            && self::moveToNextLine($lines)
            && '|' === substr(current($lines), 0, 1)
            && self::moveToPrevLine($lines)
        ) {
            $value = $line;

            while (
                self::moveToNextLine($lines)
                && ('+' === substr(current($lines), 0, 1)
                    || '|' === substr(current($lines), 0, 1)
                )
            ) {
                $value .= "\n".current($lines);
            }

            return Token::gridTable()->withValue($value);
        }

        /* Simple Tables
         */
        if (preg_match('/^[=\s]+$/', $line)) {
            $type = 'table';
            $value = $line;
            $i = 0;

            while (self::moveToNextLine($lines)) {
                if (preg_match('/^[=\s]+$/', current($lines))) {
                    $i++;
                }

                $value .= "\n".current($lines);

                if ($i == 2) {
                    break;
                }
            }

            return Token::simpleTable()->withValue($value);
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
        $indent = self::getIndent(self::getPrevNonWhitespaceLine($lines));
        if (self::isIndentedHigher($line, $indent)) {
            $indent = self::getIndent($line);
            $value = [substr($line, $indent)];

            while (
                self::moveToNextLine($lines)
                && (self::isBlank(current($lines))
                    || self::isIndentedEquallyOrHigher(current($lines), $indent)
                )
            ) {
                $value[] = substr(current($lines), $indent);

                if (
                    false !== strpos(current($lines), '--')
                    || false !== strpos(current($lines), '—')
                ) {
                    break;
                }
            }

            self::prevIfLastLineIsBlank($lines, $value);

            return Token::blockQuote()->withValue(implode("\n", $value))->atOffset($indent);
        }

        /* Footnotes
         *
         * +-------+-------------------------+
         * | ".. " | "[" label "]" footnote  |
         * +-------+                         |
         *         | (body elements)+        |
         *         +-------------------------+
         */
        if (preg_match('/^(\s*)\.\.\s\[([0-9]+|#|\*|#'.$simpleNameRegex.')\]/', $line, $matches)) {
            $value = [$line];
            $indent = strlen($matches[1]) + 3;

            while (
                self::moveToNextLine($lines)
                && (self::isBlank(current($lines))
                    || self::isIndentedEquallyOrHigher(current($lines), $indent)
                )
            ) {
                $value[] = current($lines);
            }

            self::prevIfNotStartOfFile($lines);

            return Token::footnote()->withValue(implode("\n", $value));
        }

        /* Hyperlink Targets
         *
         * +-------+----------------------+
         * | ".. " | "_" name ":" link    |
         * +-------+ block                |
         *         |                      |
         *         +----------------------+
         */
        if (preg_match('/^(\s*)\.\.\s_`?(?:'.$simpleNameRegex.'|[\w\\:\s]+)`?:/', $line, $matches)) {
            $value = $line;
            $indent = strlen($matches[1]) + 3;

            while (
                self::moveToNextLine($lines)
                && self::isIndentedEqually(current($lines), $indent)
            ) {
                $value .= "\n".current($lines);
            }

            self::prevIfNotStartOfFile($lines);

            return Token::hyperlinkTarget()->withValue($value);
        }

        /* Comments
         *
         * +-------+----------------------+
         * | ".. " | comment              |
         * +-------+ block                |
         *         |                      |
         *         +----------------------+
         */
        if (preg_match('/^\s*\.\.(?:\s|$)/', $line)) {
            $value = [$line];
            $indent = self::getIndent($line);

            if (
                self::moveToNextLine($lines)
                && (self::isBlank(current($lines))
                    || self::isIndentedHigher(current($lines), $indent)
                )
            ) {
                $value[] = current($lines);
                $indent = self::getIndent(current($lines));


                while (
                    self::moveToNextLine($lines)
                    && (self::isBlank(current($lines))
                        || self::isIndentedEquallyOrHigher(current($lines), $indent)
                    )
                ) {
                    $value[] = current($lines);
                }

                self::prevIfNotStartOfFile($lines);
            } else {
                prev($lines);
            }

            return Token::comment()->withValue(implode("\n", $value));
        }

        /* fixme: this just takes other lines
         *
         * Definition Lists
         *
         * +----------------------------+
         * | term [ " : " classifier ]* |
         * +--+-------------------------+--+
         *    | definition                 |
         *    | (body elements)+           |
         *    +----------------------------+
         *
        $indent = self::getIndent($line);
        if (
            self::moveToNextLine($lines)
            && !self::isBlank(current($lines))
            && self::isIndentedHigher(current($lines), $indent)
        ) {
            $value = [$line, current($lines)];
            $indent = self::getIndent(current($lines));

            while (
                self::moveToNextLine($lines)
                && (self::isBlank(current($lines))
                    || self::isIndentedEquallyOrHigher(current($lines), $indent)
                )
            ) {
                $value[] = current($lines);
            }

            prev($lines);

            self::prevIfLastLineIsBlank($lines, $value);

            return Token::definitionList()->withValue(implode("\n", $value));
        } else {
            self::prevIfNotStartOfFile($lines);
        }*/

        /* Section Titles
         */
        if (preg_match('/^\s*([!"#$%&\'()*+,-.\/:;<=>?@[\]^_`{|}\~])\1+\s*$/', next($lines))) {
            return Token::sectionTitle()->withValue($line."\n".current($lines));
        } else {
            prev($lines);
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
        $value = $line;
        $indent = strlen($line) - strlen(ltrim($line));
        while (
            self::moveToNextLine($lines)
            && self::isIndentedEqually(current($lines), $indent)
        ) {
            $value .= "\n".current($lines);
        }

        self::prevIfNotStartOfFile($lines);

        return Token::paragraph()->withValue($value);
    }

    private static function moveToNextLine(&$lines)
    {
        return false !== next($lines);
    }

    private static function moveToPrevLine(&$lines)
    {
        return false !== prev($lines);
    }

    private static function prevIfNotStartOfFile(&$lines)
    {
        if (false !== current($lines)) {
            prev($lines);
        }
    }

    private static function isBlank($line)
    {
        return '' === trim($line);
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

    private static function getPrevNonWhitespaceLine($lines)
    {
        while (self::moveToPrevLine($lines) && self::isBlank(current($lines)));

        return current($lines);
    }

    private static function prevIfLastLineIsBlank(&$lines, array &$value)
    {
        if (self::isBlank($line = array_pop($value))) {
            self::prevIfNotStartOfFile($lines);
        } else {
            $value[] = $line;
        }
    }
}
