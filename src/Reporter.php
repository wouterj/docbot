<?php

namespace Docbot;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * A reporter transforms the constraint violation list to a resource (e.g. console, file, etc).
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
interface Reporter
{
    const SUCCESS = 0;
    const NOTICE  = 1;
    const WARNING = 2;
    const ERROR   = 4;

    /**
     * @return int One of the result constants
     */
    public function handle(ConstraintViolationListInterface $constraintViolationList, Text $file);
}
