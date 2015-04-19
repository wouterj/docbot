<?php

namespace Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class Base extends ConstraintValidator
{
    /** @var null|int */
    private $lineNumber;

    /** {@inheritdoc} */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Text) {
            throw new \InvalidArgumentException('Reviewers can only review Text instances, "%s" given.', gettype($value));
        }

        $this->review($value);
    }

    /**
     * Default implementation of the review method.
     *
     * The default implementation iterates over each line and
     * let the reviewer review that line.
     *
     * @param Text $file
     */
    public function review(Text $file)
    {
        $file->map(array($this, 'doReviewLine'));
    }

    public function doReviewLine($line, $lineNumber, Text $file)
    {
        $this->lineNumber = $lineNumber;

        $this->reviewLine($line, $lineNumber, $file);
    }

    protected function addError($message, array $parameters = array(), $lineNumber = null)
    {
        $violation = $this->context->buildViolation($message)
            ->atPath('lines['.(null === $lineNumber ? ++$this->lineNumber : $lineNumber).']');

        foreach ($parameters as $name => $value) {
            $violation->setParameter($name, $value);
        }

        $violation->addViolation();
    }

    abstract public function reviewLine($line, $lineNumber, Text $file);
}
