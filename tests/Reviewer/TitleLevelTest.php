<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\TitleLevel as TitleLevelCheck;
use Gnugat\Redaktilo\File;
use Gnugat\Redaktilo\Text;
use Symfony\Component\DependencyInjection\Tests\LazyProxy\Instantiator\RealServiceInstantiatorTest;

class TitleLevelTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return TitleLevelCheck::class;
    }

    public function getExamples()
    {
        $levelMessage = 'The "%s" character should be used for a title level %d';
        $invalidMessage = 'Only =, -, ~, . and " should be used as title underlines';
        $incFile = File::fromString(<<<RST
Title level 3
~~~~~~~~~~~~~
RST
        );
        $incFile->setFilename('file.rst.inc');

        return [
            [
                Text::fromString(<<<RST
Title Level 1
=============

Title level 2
~~~~~~~~~~~~~
RST
                ),
                [$this->getViolationProphet(sprintf($levelMessage, '-', 2), 2)],
                'It finds wrongly used underline level'
            ],

            [
                Text::fromString(<<<RST
Title Level 1
+++++++++++++
RST
                ),
                [$this->getViolationProphet($invalidMessage, 2)],
                'It finds unused underline levels that are valid in reStructured Text'
            ],

            [
                Text::fromString(<<<RST
Title level 1
=============

Title level 2
-------------

Title level 3
~~~~~~~~~~~~~

Title level 4
.............

Title level 2
-------------
RST
                ),
                [],
                'It accepts jumping multiple levels back'
            ],

            [
                $incFile,
                [],
                'Inc files are allowed to start at deeper levels'
            ],
        ];
    }
}
