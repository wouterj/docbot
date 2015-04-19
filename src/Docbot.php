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

    /**
     * Lints the provided file.
     *
     * You can restrict the reviewers that are executed by passing an
     * array of types in the second argument.
     *
     * @param Text  $file
     * @param array $types
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    public function lint(Text $file, array $types = null)
    {
        return $this->validator->validate($file, null, $types ?: array('symfony', 'doc', 'rst'));
    }
}
