<?php

namespace Stoffer\Lint\Reviewer;

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
        if (preg_match('/^([\~\!\"\#\$\%\&\'\(\)\*\+,-.\\\\\/\:\;\<\=\>\?\@\[\]\^\_\`\{\|\}])\1{2,}/', $line, $data)) {
            $character = $data[1];

            $level = array_search($character, $this->levels);

            if (false === $level) {
                $this->reportError('Only =, -, ~, . and " should be used as title underlines', $line, $lineNumber + 1);

                return;
            }

            if ($level <= $this->currentLevel) {
                $this->currentLevel = $level;

                return;
            }

            if ($this->currentLevel + 1 !== $level) {
                $this->reportError(
                    'The "'.$this->levels[$this->currentLevel + 1].'" character should be used for a title level '.($this->currentLevel + 1),
                    $line,
                    $lineNumber + 1
                );
            } else {
                $this->currentLevel = $level;
            }
        }
    }
}
