<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\ShortPhpSyntax as ShortPhpSyntaxCheck;
use Gnugat\Redaktilo\Text;

class ShortPhpSyntaxTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return ShortPhpSyntaxCheck::class;
    }

    public function getExamples()
    {
        $message = 'The short syntax for PHP code (::) should be used here';

        return [
            [
                Text::fromString(<<<RST
A line ending with a nice colon:

.. code-block:: php

    echo 'You failed!';
RST
                ),
                [$this->getViolationProphet($message, 3)],
            ],

            [
                Text::fromString(<<<RST
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
                ),
                [$this->getViolationProphet($message, 3)],
                'When ending with a colon, the shorthand should be used for PHP code blocks'
            ],
        ];
    }
}
