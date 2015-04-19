<?php

namespace Docbot;

use Docbot\Reviewer\Check;
use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * The main access point for reviewing jobs.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Docbot
{
    /** @var ConstraintValidator[] */
    private $reviewers = array();
    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function addReviewer(ConstraintValidator $reviewer, $priority = 0)
    {
        $this->reviewers[] = array($priority, $reviewer);

        usort($this->reviewers, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }

            return $a[0] > $b[0] ? -1 : 1;
        });
    }

    public function lint(Text $file, array $types = null)
    {
        return $this->validator->validate($file, null, $types ?: array('symfony', 'doc', 'rst'));
    }
}
