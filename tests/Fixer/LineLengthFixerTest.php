<?php

namespace Docbot\Test\Fixer;

use Docbot\Fixer\LineLengthFixer;

class LineLengthFixerTest extends FixerTestCase
{
    protected function createFixer()
    {
        return new LineLengthFixer();
    }

    public function getExamples()
    {
        return [
            [
                <<<RST
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce purus justo, congue molestie laoreet id,
dapibus et ligula. Integer et auctor sapien, sit amet rhoncus erat. Interdum et malesuada fames ac ante
ipsum primis in faucibus. Cras non pellentesque massa. Nam posuere quam at eros imperdiet, eget vestibulum
ligula sollicitudin. Curabitur nec arcu at mauris cursus viverra vel eu leo. Nullam et pellentesque libero.
Sed nunc lacus, malesuada id metus eu, ultrices pulvinar felis. Interdum et malesuada fames ac ante ipsum
primis in faucibus. Sed id consectetur enim. Maecenas et orci et arcu sollicitudin auctor et eget justo.

.. note::

    In egestas varius est a malesuada. Donec ac neque sit amet felis fringilla feugiat. Pellentesque eget
    pellentesque lectus. Aenean ultrices turpis sed auctor ultrices. Etiam sollicitudin turpis justo, facilisis
    vehicula enim venenatis in. Pellentesque vulputate molestie metus, eget volutpat mi fermentum a. Praesent
    vestibulum vitae lacus nec dictum. Sed pulvinar augue ut leo gravida, sed bibendum enim lacinia. Ut malesuada,
    lorem rhoncus semper tempor, enim justo consequat magna, et rutrum ligula metus vitae nisi. Mauris vitae
    faucibus magna, id egestas mauris.
RST
                ,
                <<<RST
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce purus justo,
congue molestie laoreet id, dapibus et ligula. Integer et auctor sapien,
sit amet rhoncus erat. Interdum et malesuada fames ac ante ipsum primis in
faucibus. Cras non pellentesque massa. Nam posuere quam at eros imperdiet,
eget vestibulum ligula sollicitudin. Curabitur nec arcu at mauris cursus
viverra vel eu leo. Nullam et pellentesque libero. Sed nunc lacus, malesuada
id metus eu, ultrices pulvinar felis. Interdum et malesuada fames ac ante
ipsum primis in faucibus. Sed id consectetur enim. Maecenas et orci et arcu
sollicitudin auctor et eget justo.

.. note::

    In egestas varius est a malesuada. Donec ac neque sit amet felis fringilla
    feugiat. Pellentesque eget pellentesque lectus. Aenean ultrices turpis
    sed auctor ultrices. Etiam sollicitudin turpis justo, facilisis vehicula
    enim venenatis in. Pellentesque vulputate molestie metus, eget volutpat
    mi fermentum a. Praesent vestibulum vitae lacus nec dictum. Sed pulvinar
    augue ut leo gravida, sed bibendum enim lacinia. Ut malesuada, lorem
    rhoncus semper tempor, enim justo consequat magna, et rutrum ligula metus
    vitae nisi. Mauris vitae faucibus magna, id egestas mauris.
RST
                ,
                'Prose lines should not have new words after the 72th character'
            ],

            [
                'Tetaumatawhakatangihangakoauaotamateaurehaeaturipukapihimaungahoronukupokaiwhenuaakitanatahu',
                'Tetaumatawhakatangihangakoauaotamateaurehaeaturipukapihimaungahoronukupokaiwhenuaakitanatahu',
                'Long words are allowed to have more than 72 characters'
            ],

            [
                '**type**: ``string`` **default**: ``This is a long constraint error message, but it should be allowed.``',
                '**type**: ``string`` **default**: ``This is a long constraint error message, but it should be allowed.``',
                'Definitions are allowed to cross 72 characters'
            ],

            /* fixme: implement code wrapping
            [
                Text::fromString(<<<RST
.. code-block:: php

    // a line that is around 80 characters long, so there should not be an error here
    // but this should error, as it is longer than 80 characters long. Oh dear, what did I do?

Same applies to::

    // a line that is around 80 characters long, so there should not be an error here
    // but this should error, as it is longer than 80 characters long. Oh dear, what did I do?
RST
                ),
                [
                    $this->getViolationProphet($codeMessage, 4),
                    $this->getViolationProphet($codeMessage, 9),
                ],
                'Code uses a limit for 85 characters'
            ],

            [
                Text::fromString(<<<RST
    .. code-block:: php

        // a line that is around 80 characters long, so there should not be an error here
            // the same line, but now indented, so that it should give us this very nice error
RST
                ),
                [
                    $this->getViolationProphet($codeMessage, 4)
                ],
                'Indentation should be stripped from code blocks'
            ],

            [
                Text::fromString(<<<RST
.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8"?>
    <srv:container xmlns="http://symfony.com/schema/dic/security"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:srv="http://symfony.com/schema/dic/services"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
    </srv:container>
RST
                ),
                [],
                'XML schemaLocation definitions are allowed to cross the 85 character limit'
            ],*/
        ];
    }
}
