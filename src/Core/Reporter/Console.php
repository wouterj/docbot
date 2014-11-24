<?php

namespace Stoffer\Core\Reporter;

use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;
use Zend\EventManager\Event;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Console
{
    /** @var ConsoleOutput */
    private $output;
    private $errors = array();

    public function __construct()
    {
        $this->output = new ConsoleOutput();
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
            '',
        ));
    }

    public function printReport(Event $event)
    {
        $helper = new FormatterHelper();
        if (0 === $count = count(array_merge($this->errors))) {
            $this->output->writeln($helper->formatBlock('[OK] There where no errors founds', 'bg=green', true));
        } else {
            foreach ($this->errors as $line => $errors) {
                $this->output->writeln('<comment>['.$line.']</comment> '.$errors['_line']);
                $errors['_line'] = '';
                $errors[] = '';
                $indent = str_repeat(' ', 1 + strlen($line));
                $this->output->writeln(array_map(function ($error) use ($indent) {
                    if (!$error) {
                        return;
                    }

                    return $indent.'- <fg=red>'.$error.'</fg=red>';
                }, $errors));
            }

            $this->output->writeln($helper->formatBlock(' [ERROR] '.$count.' errors are found', 'bg=red', true));
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
}
