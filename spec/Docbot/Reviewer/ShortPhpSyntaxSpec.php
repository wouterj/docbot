<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class ShortPhpSyntaxSpec extends ReviewerBehaviour
{
    function it_finds_places_where_short_syntax_should_be_used(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The short syntax for PHP code (::) should be used here', 3
        );

        $this->review(Text::fromArray(array(
            'A file ending with a nice colon:',
            '',
            '.. code-block:: php',
        )));
    }

    function it_does_not_error_on_correct_long_syntax_usages(ExecutionContextInterface $context)
    {
        PredictThatReviewer::shouldNotReportAnyError($context);

        $this->review(Text::fromArray(array(
            'A file without a colon.',
            '',
            '.. code-block:: php',
        )));
    }

    function it_does_ignore_whitespace(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The short syntax for PHP code (::) should be used here', 5
        );

        $this->review(Text::fromArray(array(
            '    A line ending with a colon:   ',
            '',
            '',
            "\t",
            '    .. code-block:: php'
        )));
    }
}
