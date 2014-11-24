<?php

namespace Stoffer\Lint\Reviewer;

class DirectiveWhitespace extends Base
{
    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/^\s*\.\. ([\w-]+)::|::$/', $line, $data)) {
            $nextLine = $file->getLine($lineNumber + 1);

            if (isset($data[1]) && 'versionadded' === $data[1]) {
                if (trim($nextLine) === '') {
                    $this->reportError(
                        'There should be no empty line between the start of a versionadded directive and the body',
                        $line,
                        $lineNumber + 3
                    );
                }

                return;
            }

            if (':' === substr(trim($nextLine), 0, 1) || (isset($data[1]) && 'index' === $data[1])) {
                return;
            }

            if (trim($nextLine) !== '') {
                $this->reportError(
                    'There should be an empty line between the body and the start of a directive (except from versionadded directives)',
                    $line,
                    $lineNumber + 2
                );
            }
        }
    }
}
