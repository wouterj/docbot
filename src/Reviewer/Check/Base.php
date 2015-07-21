<?php

namespace Docbot\Reviewer\Check;

use Symfony\Component\Validator\Constraint;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Base extends Constraint
{
    const PAYLOAD_EXPERIMENTAL = 'experimental';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return str_replace('Check\\', '', get_class($this));
    }
}
