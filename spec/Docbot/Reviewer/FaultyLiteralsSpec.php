<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class FaultyLiteralsSpec extends ReviewerBehaviour
{
    function it_finds_single_backtick_usage(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'Found unrecognized usage of backticks. Did you mean to create a link (`%value%`_) or a literal (``%value%``)?', 1,
            array('%value%' => 'wrong')
        );

        $this->review(Text::fromString('This is probably some `wrong` backtick usage.'));
    }
}
