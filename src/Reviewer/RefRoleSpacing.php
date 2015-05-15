<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;

class RefRoleSpacing extends Base
{
    public function reviewLine($line, $lineNumber, Text $file)
    {
        preg_match_all('/:ref:`(?P<label>[^<]+)(?P<ref><[^>]+>)`/', $line, $matches, PREG_SET_ORDER);

        foreach ($matches as $reference) {
            if (substr($reference['label'], -1) !== ' ') {
                $this->addError('There should be a space between "%label%" and "%ref%".', array(
                    '%label%' => $reference['label'],
                    '%ref%' => $reference['ref'],
                ));
            }
        }
    }
}
