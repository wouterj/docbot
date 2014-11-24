<?php

namespace Stoffer\Lint\Diff;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class LineDiff implements Diff
{
    private $before;
    private $after;
    private $lineNumber;

    public function __construct($before, $after, $lineNumber)
    {
        $this->before = $before;
        $this->after = $after;
        $this->lineNumber = $lineNumber;
    }

    public function getAdditions()
    {
        return array($this->after);
    }

    public function getDeletions()
    {
        return array($this->before);
    }

    public function getDiff()
    {
        return sprintf(
            "- %s\n+ %s",
            $this->getDeletions(),
            $this->getAdditions()
        );
    }
}
