<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class TitleCaseSpec extends ReviewerBehaviour
{
    function it_find_words_that_should_be_capitialized(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'All words, except from closed-class words, have to be capitalized: "%correct_title%"', 1,
            array('%correct_title%' => 'A Wrong Capitalized Title of a Section')
        );

        $this->review(Text::fromArray(array(
            'A wrong capitalized title of A section',
            '==========================',
        )));
    }
}
