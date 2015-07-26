<?php

namespace Docbot\Test\Fixer;

use Docbot\Fixer\UnstyledDirectivesFixer;

class UnstyledDirectivesFixerTest extends FixerTestCase
{
    protected function createFixer()
    {
        return new UnstyledDirectivesFixer();
    }

    public function getExamples()
    {
        return [
            [
                <<<RST
.. warning::

    I am stoffing you!

.. caution::

    Yes, prefect.

.. danger::

    I may put a comment on each line of your PR

.. hint::

    Read the contributing guidelines.
RST
                ,
                <<<RST
.. caution::

    I am stoffing you!

.. caution::

    Yes, prefect.

.. caution::

    I may put a comment on each line of your PR

.. tip::

    Read the contributing guidelines.
RST
                ,
                'Usage of unstyled directives is not allowed.'
            ],
        ];
    }
}
