<?php

namespace spec\Docbot;

use Gnugat\Redaktilo\Text;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class DocbotSpec extends ObjectBehavior
{
    function let(ValidatorInterface $validator)
    {
        $this->beConstructedWith($validator);
    }

    function it_lints_files($validator, Text $text)
    {
        $validator->validate($text, Argument::cetera())->shouldBeCalled();

        $this->lint($text);
    }

    function it_defaults_to_all_types($validator, Text $text)
    {
        $validator->validate($text, null, array('symfony', 'doc', 'rst'))->shouldBeCalled();

        $this->lint($text);
    }

    function it_can_test_only_certain_types($validator, Text $text)
    {
        $validator->validate($text, null, array('doc'))->shouldBeCalled();

        $this->lint($text, array('doc'));
    }
}
