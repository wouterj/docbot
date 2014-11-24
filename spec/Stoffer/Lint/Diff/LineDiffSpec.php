<?php

namespace spec\Stoffer\Lint\Diff;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Stoffer\Lint\Diff\Diff;

class LineDiffSpec extends ObjectBehavior
{
    function it_generates_diff_for_one_line()
    {
        $this->beConstructedWith('Line before', 'Line after', 12);

        $this->getDiff()->shouldReturn(array(array(12, Diff::DELETION, 'Line before'));

        $this->getAdditions()->shouldReturn(array(12 => 'Line after'));
        $this->getDeletions()->shouldReturn(array(12 => 'Line before'));
    }
}
