<?php

namespace Stoffer\Event;

use Zend\EventManager\Event;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ReportError extends Event
{
    const EVENT = 'error_reported';

    private $message;
    private $line;
    private $lineNumber;
    private $filename;

    public function __construct($message, $line, $lineNumber, $filename)
    {
        $this->message = $message;
        $this->line = $line;
        $this->lineNumber = $lineNumber;
        $this->filename = $filename;

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

    public function getFilename()
    {
        return $this->filename;
    }
}
