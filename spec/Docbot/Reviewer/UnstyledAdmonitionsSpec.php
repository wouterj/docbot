<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UnstyledAdmonitionsSpec extends ReviewerBehaviour
{
    function it_finds_unstyled_directives(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The "%type%" directive is not styled on symfony.com', 1,
            array('%type%' => 'warning')
        );
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The "%type%" directive is not styled on symfony.com', 5,
            array('%type%' => 'danger')
        );

        $this->review(Text::fromArray(array(
            '.. warning::',
            '',
            '    I am stoffing you!',
            '',
            '.. danger::',
            '',
            '    I may put a comment on each line of your PR',
        )));
    }
}
