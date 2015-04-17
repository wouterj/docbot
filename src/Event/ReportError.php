<?php

namespace Docbot\Event;

use Zend\EventManager\Event;

/**
 * An event that's triggered when a reviewer found an error.
 *
 * @author Wouter J <wouter@wouterj.nl>
 */
class ReportError extends Event
{
    const EVENT = 'error_reported';

    private $message;
    private $line;
    private $lineNumber;
    private $file;

    public function __construct($message, $line, $lineNumber, $file)
    {
        $this->message = $message;
        $this->line = $line;
        $this->lineNumber = $lineNumber;
        $this->file = $file;

        parent::__construct(self::EVENT, 'reviewer');
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getLineNumber()
    {
        return $this->lineNumber;
    }

    public function getFile()
    {
        return $this->file;
    }
}
