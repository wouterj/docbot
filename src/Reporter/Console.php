<?php

namespace Stoffer\Reporter;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\EventManager\Event;

/**
 * An output printer that prints into the console.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class Console
{
    /** @var OutputInterface */
    private $output;
    private $errors = array();
    /** @var FormatterHelper */
    private $formatterHelper;

    public function __construct(OutputInterface $output)
    {
        $this->formatterHelper = new FormatterHelper();
        $this->output = $output;
    }

    public function printFileName(Event $event)
    {
        $this->errors = array();

        $filename = str_replace(getcwd(), '', $event->getParams()->file->getFilename());
        $this->output->writeln(array(
            '<fg=blue>',
            $filename,
            str_repeat('=', strlen($filename)),
            '</fg=blue>',
        ));
    }

    public function printReport(Event $event)
    {
        $errorCount = $this->getErrorCount();

        if (0 === $errorCount) {
            $this->outputBlock('There where no errors founds');
        } else {
            foreach ($this->errors as $lineNumber => $errors) {
                $this->output->writeln('<comment>['.$lineNumber.']</comment> "'.$errors['_line'].'"');

                unset($errors['_line']);
                $indent = 3 + strlen($lineNumber);
                $errors = $this->indentErrorMessages($errors, $indent);
                $this->output->writeln($errors);
                $this->output->writeln('');
            }

            $this->outputBlock($errorCount.' errors are found', false);
        }

        $this->output->writeln('');
    }

    public function collectErrors(Event $event)
    {
        $params = $event->getParams();
        $lineNumber = $params->lineNumber;

        if (!isset($this->errors[$lineNumber])) {
            $this->errors[$lineNumber] = array('_line' => $params->line);
        }

        $this->errors[$lineNumber][] = $params->message;
    }

    private function outputBlock($message, $success = true)
    {
        $color = $success ? 'green' : 'red';
        $message = ($success ? '[OK]' : '[ERROR]').' '.$message;

        $this->output->writeln($this->formatterHelper->formatBlock($message, 'bg='.$color, true));
    }

    private function getErrorCount()
    {
        return count(array_merge($this->errors));
    }

    /**
     * @return array
     */
    private function indentErrorMessages(array $errors, $indent)
    {
        $indent = str_repeat(' ', $indent);

        return array_map(
            function ($error) use ($indent) {
                if (!$error) {
                    return;
                }

                return $indent.'- <fg=red>'.$error.'</fg=red>';
            },
            $errors
        );
    }
}
