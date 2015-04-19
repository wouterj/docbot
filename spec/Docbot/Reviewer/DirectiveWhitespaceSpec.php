<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DirectiveWhitespaceSpec extends ReviewerBehaviour
{
    function it_wants_an_empty_line_after_admonitions(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'There should be an empty line between the body and the start of a directive (except from versionadded directives)', 2
        );
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'There should be an empty line between the body and the start of a directive (except from versionadded directives)', 9
        );

        $this->review(Text::fromArray(array(
            '.. note::',
            '    The contents of the note.',
            '',
            '.. code-block::',
            '',
            '    // this is correct',
            '',
            'Hello::',
            '    // this is not',
        )));
    }

    function it_wants_the_body_direct_after_an_versionadded_directive(ExecutionContextInterface $context)
    {
        PredictThatReviewer::shouldNotReportAnyError($context);

        $this->review(Text::fromArray(array(
            '.. versionadded:: 2.3',
            '    The feature X was introduced in XX.',
        )));
    }

    function it_errors_when_the_versionadded_body_is_not_directly_after_start(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'There should be no empty line between the start of a versionadded directive and the body', 3
        );

        $this->review(Text::fromArray(array(
            '.. versionadded:: 2.3',
            '',
            '    The feature X was introduced in XX.',
        )));
    }
}
