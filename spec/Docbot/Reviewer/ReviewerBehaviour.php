<?php

namespace spec\Docbot\Reviewer;

use PhpSpec\ObjectBehavior;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class ReviewerBehaviour extends ObjectBehavior
{
    function let(ExecutionContextInterface $context)
    {
        $this->initialize($context);
    }
}
