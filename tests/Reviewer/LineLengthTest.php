<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Reviewer\Check\LineLength as LineLengthCheck;
use Gnugat\Redaktilo\Text;
use Symfony\Component\DependencyInjection\Tests\LazyProxy\Instantiator\RealServiceInstantiatorTest;

class LineLengthTest extends BaseReviewerTest
{
    protected function getReviewerClass()
    {
        return LineLengthCheck::class;
    }

    public function getExamples()
    {
        $proseMessage = 'A line should be wrapped after the first word that crosses the 72th character';
        $codeMessage = 'In order to avoid horizontal scrollbars, you should wrap the code on a 85 character limit';

        return [
            [
                Text::fromString(<<<RST
A line that does not reach the limit
A line that goes over the limit with a lot of words, as you can see in this sentence...
RST
                ),
                [$this->getViolationProphet($proseMessage, 2)],
                'Prose lines should not have new words after the 72th character'
            ],

            [
                Text::fromString('Tetaumatawhakatangihangakoauaotamateaurehaeaturipukapihimaungahoronukupokaiwhenuaakitanatahu'),
                [],
                'Long words are allowed to have more than 72 characters'
            ],

            [
                Text::fromString('**type**: ``string`` **default**: ``This is a long constraint error message, but it should be allowed.``'),
                [],
                'Definitions are allowed to cross 72 characters'
            ],

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
            ],
        ];
    }
}
