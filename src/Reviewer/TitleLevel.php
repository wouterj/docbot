<?php

namespace Docbot\Reviewer;

/**
 * A reviewer checking the correct title level usages.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class TitleLevel extends Base
{
    private $levels = array(
        1 => '=',
        2 => '-',
        3 => '~',
        4 => '.',
        5 => '"',
    );
    private $currentLevel = 1;

    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/^([\~\!\"\#\$\%\&\'\(\)\*\+,-.\\\\\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}])\1{3,}/', $line, $data)) {
            $character = $data[1];

            $level = array_search($character, $this->levels);

            if (false === $level) {
                $this->reportError('Only =, -, ~, . and " should be used as title underlines');

                return;
            }

            if ($level <= $this->currentLevel) {
                $this->currentLevel = $level;

                return;
            }

            if ($this->currentLevel + 1 !== $level) {
                $this->reportError(sprintf(
                    'The "%s" character should be used for a title level %s',
                    $this->levels[$this->currentLevel + 1], $this->currentLevel + 1
                ));
            } else {
                $this->currentLevel = $level;
            }
        }
    }
}
