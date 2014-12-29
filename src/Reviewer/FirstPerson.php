<?php

namespace Stoffer\Reviewer;

class FirstPerson extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/\b(I(?!\.)|we|let\'s)\b/i', $line)) {
            $this->reportError(
                'The first person ("I", "we", "let\'s") should always be avoided',
                $line,
                $file,
                $lineNumber + 1
            );
        }
    }
}
