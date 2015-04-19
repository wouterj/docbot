<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\Text;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;

class LineLengthSpec extends ReviewerBehaviour
{
    function it_errors_when_a_new_word_is_after_the_72th_character(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'A line should be wrapped after the first word that crosses the 72th character', 2
        );

        $this->review(Text::fromArray(array(
            'A line that does not reach the limit',
            'A line that goes over the limit with a lot of words, as you can see in this sentence...',
        )));
    }

    function it_does_not_error_when_a_long_word_crosses_the_limit(ExecutionContextInterface $context)
    {
        PredictThatReviewer::shouldNotReportAnyError($context);

        $this->review(Text::fromString('Tetaumatawhakatangihangakoauaotamateaurehaeaturipukapihimaungahoronukupokaiwhenuaakitanatahu'));
    }

    function it_does_use_a_85_characters_limit_for_code(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'In order to avoid horizontal scrollbars, you should wrap the code on a 85 character limit', 4
        );

        $this->review(Text::fromArray(array(
            '.. code-block:: php',
            '',
            '    // a line that is around 80 characters long, so there should not be an error here',
            '    // but this should error, as it is longer than 80 characters long. Oh dear, what did I do?',
        )));
    }
}
