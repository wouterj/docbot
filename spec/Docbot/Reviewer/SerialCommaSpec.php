<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class SerialCommaSpec extends ReviewerBehaviour
{
    function it_finds_serial_commas(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'Serial (Oxford) comma\'s should be avoided: "[...] %word% %conjunction% [...]"', 1,
            array('%word%' => 'bar', '%conjunction%' => 'and')
        );

        $this->review(Text::fromString('One has foo, bar, and cat.'));
    }
}
