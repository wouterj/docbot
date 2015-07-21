<?php

namespace Docbot\Reporter;

use Docbot\Reporter;
use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class SortReporter implements Reporter
{
    /** @var Reporter */
    private $reporter;

    public function __construct(Reporter $reporter)
    {
        $this->reporter = $reporter;
    }

    public function setVerbosity($level)
    {
        $this->reporter->setVerbosity($level);
    }

    /**
     * @return int One of the result constants
     */
    public function handle(ConstraintViolationListInterface $constraintViolationList, Text $file)
    {
        $constraintViolationList = iterator_to_array($constraintViolationList);
        $that = $this;
        usort($constraintViolationList, function (ConstraintViolation $a, ConstraintViolation $b) use ($that) {
            $aNumber = $that->getLineNumber($a);
            $bNumber = $that->getLineNumber($b);

            if ($aNumber === $bNumber) {
                return 0;
            }

            return $aNumber > $bNumber ? 1 : -1;
        });

        return $this->reporter->handle($constraintViolationList, $file);
    }

    /** @internal */
    public function getLineNumber(ConstraintViolation $violation)
    {
        if (!preg_match('/lines\[(\d+)\]/', $violation->getPropertyPath(), $matches)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot get line number of string "%s". Format has to be: line[%line_number%]',
                $violation->getPropertyPath()
            ));
        }

        return intval($matches[1]);
    }
}
