<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TrailingWhitespaceSpec extends ReviewerBehaviour
{
    function it_finds_lines_with_only_whitespace(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'There should be no trailing whitespace at the end of a line', 2
        );

        $this->review(Text::fromArray(array(
            'a file with just normal text',
            '     ',
            'followed by nasty trailing whitespace',
            '',
            'and a simple empty line.',
        )));
    }

    function it_finds_trailing_whitespace_at_the_end_of_a_line(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'There should be no trailing whitespace at the end of a line', 1
        );
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'There should be no trailing whitespace at the end of a line', 2
        );

        $this->review(Text::fromArray(array(
            'a line with whitespace after it    ',
            "a tab is also whitespace\t",
        )));
    }
}
