<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class FirstPersonSpec extends ReviewerBehaviour
{
    function it_finds_first_person_usage(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The first person ("I", "we", "let\'s") should always be avoided', 1
        );
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The first person ("I", "we", "let\'s") should always be avoided', 3
        );

        $this->review(Text::fromArray(array(
            'I wrote this line!',
            'In this line is everything correct.',
            'But let\'s screw it up here.',
        )));
    }
}
