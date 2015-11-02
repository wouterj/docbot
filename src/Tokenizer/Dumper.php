<?php

namespace Docbot\Tokenizer;

/**
 * Dumps a Token stream to a reStructured Text string.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Dumper
{
    public static function dump(Tokens $tokens)
    {
        $str = '';

        foreach ($tokens as $token) {
            $str .= $token->indentedValue();
        }

        return $str;
    }
}
