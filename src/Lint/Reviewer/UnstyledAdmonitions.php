<?php

namespace Stoffer\Lint\Reviewer;

use Stoffer\Lint\Editor;
use Zend\EventManager\Event;

class UnstyledAdmonitions extends Base
{
    static protected $unStyledAdmonitions = array('attention', 'danger', 'error', 'hint', 'important', 'warning', 'topic');

    public function reviewLine($line, $lineNumber, $file)
    {
        if (preg_match('/^.. ([\w-]+)::/', trim($line), $data)) {
            if (in_array($data[1], self::$unStyledAdmonitions)) {
                $this->reportError(
                    'The "'.$data[1].'" directive is not styled on symfony.com',
                    $line,
                    $lineNumber + 1
                );
            }
        }
    }
}
