<?php

namespace Docbot\Reporter;

use Docbot\Reporter;
use Docbot\Reviewer\Check\Base;
use Gnugat\Redaktilo\File;
use Gnugat\Redaktilo\Text;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * A reporter that prints into the console.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Console implements Reporter
{
    /** @var OutputInterface */
    private $output;
    /** @var OutputInterface */
    private $cliOutput;
    /** @var FormatterHelper */
    private $formatterHelper;
    private $verbosity = 1;

    public function __construct(OutputInterface $output)
    {
        $this->formatterHelper = new FormatterHelper();
        $this->cliOutput = $this->output = $output;
    }

    public function setVerbosity($level)
    {
        if ($level < 0 || $level > 4) {
            throw new \InvalidArgumentException('Verbosity level '.$level.' unknown.');
        }

        if ($level === self::VERBOSITY_NONE) {
            $this->output = new NullOutput();
        }

        $this->verbosity = $level;
    }

    public function handle(ConstraintViolationListInterface $constraintViolationList, Text $file)
    {
        $errorCount = count($constraintViolationList);

        if ($file instanceof File) {
            if ($errorCount === 0) {
                if ($this->verbosity === self::VERBOSITY_ALL) {
                    $this->printFilename($file);
                }
            } elseif ($this->verbosity !== self::VERBOSITY_NONE) {
                $this->printFilename($file);
            }
        }

        if (0 === $errorCount) {
            if ($this->verbosity > self::VERBOSITY_ERROR) {
                $this->output->writeln('');
                $this->outputBlock('Perfect! No errors were found.');
            }

            return self::SUCCESS;
        }

        $currentLineNumber = 0;
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

        $onlyExperimental = true;
        foreach ($constraintViolationList as $violation) {
            if (Base::PAYLOAD_EXPERIMENTAL !== $violation->getConstraint()->payload) {
                $onlyExperimental = $experimental = false;
            } else {
                $experimental = true;
            }

            $lineNumber = $this->getLineNumber($violation);
            if ($lineNumber !== $currentLineNumber) {
                $this->output->writeln('');
                $this->printLine($file, $lineNumber);

                $currentLineNumber = $lineNumber;
            }

            $indent = 3 + strlen($lineNumber);
            $this->printError($violation, $experimental, $indent);
        }

        $this->output->writeln('');
        $this->outputBlock(sprintf('Found %d error%s.', $errorCount, $errorCount === 1 ? '' : 's'), false);

        return $onlyExperimental ? self::NOTICE : self::ERROR;
    }

    private function outputBlock($message, $success = true)
    {
        $color = $success ? 'green' : 'red';
        $message = ($success ? '[OK]' : '[ERROR]').' '.$message;

        $this->output->writeln($this->formatterHelper->formatBlock($message, 'bg='.$color, true));
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

    private function printFilename(File $file)
    {
        $filename = str_replace(getcwd(), '', $file->getFilename());

        $this->output->writeln(array(
            '<fg=blue>',
            $filename,
            str_repeat('=', strlen($filename)).'</>',
        ));
    }

    private function printLine(Text $file, $lineNumber)
    {
        $line = $file->getLine($lineNumber - 1);

        $this->output->writeln('<comment>['.$lineNumber.']</comment> "'.$line.'"');
    }

    private function printError(ConstraintViolation $violation, $experimental, $indent)
    {
        $this->output->writeln(str_repeat(' ', $indent).'- <fg='.($experimental ? 'yellow' : 'red').'>'.($experimental ? '(experimental) ' : '').$violation->getMessage().'</>');
    }
}
