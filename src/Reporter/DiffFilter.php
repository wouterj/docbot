<?php

namespace Docbot\Reporter;

use Docbot\Reporter;
use Gnugat\Redaktilo\File;
use Gnugat\Redaktilo\Text;
use SebastianBergmann\Diff\Diff;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class DiffFilter implements Reporter
{
    /** @var Reporter */
    private $reporter;
    /** @var Diff[] */
    private $diff;

    /**
     * @param Reporter $reporter
     * @param Diff[]   $diff
     */
    public function __construct(Reporter $reporter, array $diff)
    {
        $this->reporter = $reporter;
        $this->diff = $diff;
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
        $diff = null;
        if ($file instanceof File) {
            foreach ($this->diff as $d) {
                if (substr($d->getTo(), 2) === $file->getFilename()) {
                    $diff = $d;
                    break;
                }
            }

            if (null === $diff) {
                return Reporter::SUCCESS;
            }
        }

        foreach ($constraintViolationList as $offset => $violation) {
            /** @var ConstraintViolation $violation */
            $lineNumber = $this->getLineNumber($violation);
            foreach ($diff->getChunks() as $chunk) {
                if ($chunk->getEnd() > $lineNumber || ($chunk->getEnd() + $chunk->getEndRange()) < $lineNumber) {
                    $constraintViolationList->remove($offset);
                }
            }
        }

        return $this->reporter->handle($constraintViolationList, $file);
    }

    private function getLineNumber(ConstraintViolation $violation)
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
