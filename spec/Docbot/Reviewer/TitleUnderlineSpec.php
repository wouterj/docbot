<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class TitleUnderlineSpec extends ReviewerBehaviour
{
    function it_finds_title_underlines_which_are_too_short(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The underline of a title should have exact the same length as the text of the title', 2
        );

        $this->review(Text::fromArray(array(
            'A title',
            '====',
        )));
    }

    function it_finds_title_underlines_which_are_too_long(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The underline of a title should have exact the same length as the text of the title', 2
        );

        $this->review(Text::fromArray(array(
            'A title',
            '==========='
        )));
    }
}
