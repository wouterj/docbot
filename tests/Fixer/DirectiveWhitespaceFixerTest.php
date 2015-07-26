<?php

namespace Docbot\Test\Fixer;

use Docbot\Fixer\DirectiveWhitespaceFixer;

class DirectiveWhitespaceFixerTest extends FixerTestCase
{
    protected function createFixer()
    {
        return new DirectiveWhitespaceFixer();
    }

    public function getExamples()
    {
        return [
            [
                <<<RST
.. note::
    The contents of the note.

.. code-block::

    // correct!
RST
                ,
                <<<RST
.. note::

    The contents of the note.

.. code-block::

    // correct!
RST
                ,
                'Directive markers should be followed by a blank line'
            ],

            [
                <<<RST
Let's try one more time::
    // come on, you know better...
RST
                ,
                <<<RST
Let's try one more time::

    // come on, you know better...
RST
                ,
                'Literal blocks require a blank line'
            ],

            [
                <<<RST
.. versionadded:: 2.5
    Do you know what was added in 2.5?

.. versionadded:: 2.5

    Do you know what was added in 2.5?
RST
                ,
                <<<RST
.. versionadded:: 2.5
    Do you know what was added in 2.5?

.. versionadded:: 2.5
    Do you know what was added in 2.5?
RST
                ,
                'Version added directives should not be followed by a blank line'
            ],

            [
                <<<RST
.. code-block:: php
    :linenums:

    // some code!
RST
                ,
                <<<RST
.. code-block:: php
    :linenums:

    // some code!
RST
                ,
                'Directive options are allowed to be placed directly after the start'
            ],

            [
                <<<RST
.. index::
    single: Hello

.. index::

    single: Hello
RST
                ,
                <<<RST
.. index::
    single: Hello

.. index::
    single: Hello
RST
                ,
                'Index directives should not be followed by a blank line'
            ],
        ];
    }
}
