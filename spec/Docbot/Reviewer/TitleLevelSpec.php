<?php

namespace spec\Docbot\Reviewer;

use Gnugat\Redaktilo\File;
use Gnugat\Redaktilo\Text;
use spec\helpers\Prediction\Reviewer as PredictThatReviewer;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class TitleLevelSpec extends ReviewerBehaviour
{
    function it_finds_wrong_level_characters(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'The "%underline_char%" character should be used for a title level %level%', 5,
            array('%underline_char%' => '-', '%level%' => 2)
        );

        $this->review(Text::fromArray(array(
            'Title Level 1',
            '=============',
            '',
            'Title level 2',
            '~~~~~~~~~~~~~'
        )));
    }

    function it_finds_unused_underline_characters(ExecutionContextInterface $context, ConstraintViolationBuilderInterface $builder)
    {
        PredictThatReviewer::shouldReportError(
            $context, $builder,
            'Only =, -, ~, . and " should be used as title underlines', 2
        );

        $this->review(Text::fromArray(array(
            'Title Level 1',
            '+++++++++++++',
        )));
    }

    function it_accepts_jumping_levels_up(ExecutionContextInterface $context)
    {
        PredictThatReviewer::shouldNotReportAnyError($context);

        $this->review(Text::fromArray(array(
            'Title level 1',
            '=============',
            '',
            'Title level 2',
            '-------------',
            '',
            'Title level 3',
            '~~~~~~~~~~~~~',
            '',
            'Title level 4',
            '.............',
            '',
            'Title level 2',
            '-------------',
        )));
    }

    function it_allows_inc_files_to_start_with_a_deeper_level(ExecutionContextInterface $context)
    {
        PredictThatReviewer::shouldNotReportAnyError($context);

        $file = File::fromArray(array(
            'Title level 3',
            '~~~~~~~~~~~~~',
        ));
        $file->setFilename('included_file.rst.inc');
        
        $this->review($file);
    }
}
