<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

/**
 * A reviewer checking for serial comma usage.
 *
 *  * Serial comma's SHOULD be avoided.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class SerialComma extends Base
{
    public function reviewLine($line, $lineNumber, Text $file)
    {
        if (preg_match('/(\w*), (and|or)/', $line, $matches)) {
            $this->addError(
                'Serial (Oxford) comma\'s should be avoided: "[...] %word% %conjunction% [...]"',
                array(
                    '%word%' => $matches[1],
                    '%conjunction%' => $matches[2],
                )
            );
        }
    }
}
