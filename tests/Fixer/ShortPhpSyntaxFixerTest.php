<?php

namespace Docbot\Test\Fixer;

use Docbot\Fixer\ShortPhpSyntaxFixer;

class ShortPhpSyntaxFixerTest extends FixerTestCase
{
    protected function createFixer()
    {
        return new ShortPhpSyntaxFixer();
    }

    public function getExamples()
    {
        return [
            [
                <<<RST
A line ending with a nice colon:

.. code-block:: php

    echo 'You failed!';
RST
                ,
                <<<RST
A line ending with a nice colon::

    echo 'You failed!';
RST
                ,
                'A sentence ending with a colon, followed by a PHP code block, should use the shorthand syntax.'
            ],

            [
                <<<RST
A line ending without a colon.

.. code-block:: php

    echo 'Correct!';

And one with::

.. code-block:: php

    echo 'You failed!';

And another good usage::

    echo 'Yes, you passed!';

At last:

.. code-block:: ruby

    puts 'You\'re a good documentor!';
RST
                ,
                <<<RST
A line ending without a colon.

.. code-block:: php

    echo 'Correct!';

And one with::

    echo 'You failed!';

And another good usage::

    echo 'Yes, you passed!';

At last:

.. code-block:: ruby

    puts 'You\'re a good documentor!';
RST
                ,
                'When ending with a colon, the shorthand should be used for PHP code blocks'
            ],
        ];
    }
}
