<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

/**
 * A reviewer checking for directives with no style on symfony.com
 *
 *  * Not-styled directives SHOULD be avoided.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class UnstyledAdmonitions extends Base
{
    static protected $unStyledAdmonitions = array('attention', 'danger', 'error', 'hint', 'important', 'warning', 'topic');

    public function reviewLine($line, $lineNumber, Text $file)
    {
        if (preg_match('/^.. ([\w-]+)::/', trim($line), $data)) {
            if (in_array($data[1], self::$unStyledAdmonitions)) {
                $this->addError(
                    'The "%type%" directive is not styled on symfony.com',
                    array('%type%' => $data[1])
                );
            }
        }
    }
}
