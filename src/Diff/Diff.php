<?php

namespace Stoffer\Diff;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
interface Diff
{
    const ADDITION = 1;
    const DELETION = 2;

    /**
     * Returns a list of added lines (where index is linenumber).
     *
     * @return string[]
     */
    public function getAdditions();

    /**
     * Returns a list of removed lines (where index is linenumber).
     *
     * @return string[]
     */
    public function getDeletions();

    /**
     * Gets the diff in the diff output.
     *
     * @return string
     */
    public function getDiff();
}
