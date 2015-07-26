<?php

namespace Docbot\Tests;

use Docbot\Docbot;
use Gnugat\Redaktilo\Text;
use Prophecy\Argument;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DocbotTest extends \PHPUnit_Framework_TestCase
{
    /** @var ValidatorInterface */
    private $validator;
    /** @var Docbot */
    private $docbot;
    /** @var Text */
    private $text;

    protected function setUp()
    {
        $this->validator = $this->prophesize(ValidatorInterface::class);
        $this->docbot = new Docbot($this->validator->reveal());
        $this->text = $this->prophesize(Text::class)->reveal();
    }

    public function testLintsFiles()
    {
        $this->validator->validate($this->text, Argument::cetera())->shouldBeCalled();

        $this->docbot->lint($this->text);
    }

    function testDefaultsToAllTypes()
    {
        $this->validator->validate($this->text, null, array('symfony', 'doc', 'rst'))->shouldBeCalled();

        $this->docbot->lint($this->text);
    }

    function testCanBeLimitedToSomeTypes()
    {
        $this->validator->validate($this->text, null, array('doc'))->shouldBeCalled();

        $this->docbot->lint($this->text, array('doc'));
    }
}
