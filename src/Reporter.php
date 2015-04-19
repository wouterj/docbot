<?php

namespace Docbot;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
interface Reporter
{
    const SUCCESS = 0;
    const NOTICE  = 1;
    const WARNING = 2;
    const ERROR   = 4;

    public function handle(ConstraintViolationListInterface $constraintViolationList, Text $file);
}
