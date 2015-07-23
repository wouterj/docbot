<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\DirectiveWhitespace as DirectiveWhitespaceCheck;
use Gnugat\Redaktilo\Text;

class DirectiveWhitespaceTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return DirectiveWhitespaceCheck::class;
    }

    public function getExamples()
    {
        $message = 'There should be an empty line between the body and the start of a directive (except from versionadded directives)';

        return [
            [
                Text::fromString(<<<RST
.. note::
    The contents of the note.

.. code-block::

    // correct!
RST
                ),
                [$this->getViolationProphet($message, 1)],
                'Admonitions started with `.. xxx::` require a blank line'
            ],

            [
                Text::fromString(<<<RST
Let's try one more time::
    // come on, you know better...
RST
                ),
                [$this->getViolationProphet($message, 1)],
                'Code blocks started with `::` require a blank line'
            ],

            [
                Text::fromString(<<<RST
.. versionadded:: 2.5
    Do you know what was added in 2.5?

.. versionadded:: 2.5

    Do you know what was added in 2.5?
RST
                ),
                [$this->getViolationProphet('There should be no empty line between the start of a versionadded directive and the body', 4)],
                'Version added directive should not have a blank line'
            ],

            [
                Text::fromString(<<<RST
.. code-block:: php
    :linenums:

    // some code!
RST
                ),
                [],
                'Directive options are allowed to be placed directly after the start'
            ],

            [
                Text::fromString(<<<RST
.. index::
    single: Hello

.. index::

    single: Hello
RST
                ),
                [],
                'Index directives do not require a blank line'
            ],
        ];
    }
}
