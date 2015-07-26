<?php

namespace Docbot\Fixer;

use Symfony\CS\AbstractFixer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class TrailingWhitespaceFixer extends AbstractFixer
{
    /** @inheritDoc */
    public function fix(\SplFileInfo $file, $content)
    {
        preg_match('/\R/', $content, $matches);
        
        $lines = preg_split('/\R/', $content);
        
        $lines = array_map(function ($line) {
            if (0 === strlen($line)) {
                return;
            }

            $line = rtrim($line);
            $line = preg_replace('/[\w.]\s{2,}\w/', ' ', $line);
        }, $lines);
    }

    /** @inheritDoc */
    public function getDescription()
    {
        return 'Remove trailing whitespace at the end of lines.';
    }
}
