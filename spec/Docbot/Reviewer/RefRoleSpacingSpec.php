<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class RefRoleSpacingSpec extends ReviewerBehaviour
{
    function it_errors_when_there_is_no_space_between_label_and_reference(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        foreach (array('ref', 'doc') as $role) {
            $this->testWithRole($role, $context, $builder);
        }
    }

    private function testWithRole($role, $context, $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'There should be a space between "%label%" and "%ref%".', 1,
            array(
                '%label%' => 'a label',
                '%ref%' => '<the_reference>',
            )
        );

        $this->review(Text::fromArray(array(
            ':'.$role.':`a label<the_reference>`',
        )));
    }
}
