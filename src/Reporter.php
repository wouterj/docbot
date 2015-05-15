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
    const UNKNOWN = -1;
    const SUCCESS = 0;
    const NOTICE  = 1;
    const WARNING = 2;
    const ERROR   = 4;

    const VERBOSITY_NONE = 0;
    const VERBOSITY_ERROR = 1;
    const VERBOSITY_ALL = 2;

    public function setVerbosity($level);

    /**
     * @return int One of the result constants
     */
    public function handle(ConstraintViolationListInterface $constraintViolationList, Text $file);
}
