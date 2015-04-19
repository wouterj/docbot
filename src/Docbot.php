<?php

namespace Docbot;

use Docbot\Reviewer\Check;
use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The main access point for reviewing jobs.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Docbot
{
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function lint(Text $file, array $types = null)
    {
        return $this->validator->validate($file, null, $types ?: array('symfony', 'doc', 'rst'));
    }
}
