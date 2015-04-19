<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

/**
 * A reviewer that fixes required whitespaces on directives.
 *
 *  * There SHOULD be a blank line between the directive start and the directive body;
 *  * Except from the versionadded directive, in which case there MUST NOT be a blank line.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class DirectiveWhitespace extends Base
{
    public function reviewLine($line, $lineNumber, Text $file)
    {
        if (preg_match('/^\s*\.\. ([\w-]+)::|::$/', $line, $data)) {
            $nextLine = $file->getLine($lineNumber + 1);

            if (isset($data[1]) && 'versionadded' === $data[1]) {
                if (trim($nextLine) === '') {
                    $this->addError(
                        'There should be no empty line between the start of a versionadded directive and the body',
                        array(),
                        $lineNumber + 3
                    );
                }

                return;
            }

            if (':' === substr(trim($nextLine), 0, 1) || (isset($data[1]) && 'index' === $data[1])) {
                return;
            }

            if (trim($nextLine) !== '') {
                $this->addError(
                    'There should be an empty line between the body and the start of a directive (except from versionadded directives)',
                    array(),
                    $lineNumber + 2
                );
            }
        }
    }
}
