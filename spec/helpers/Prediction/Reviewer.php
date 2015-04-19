<?php

namespace spec\helpers\Prediction;

use Prophecy\Argument;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class Reviewer
{
    public static function shouldReportError($context, $builder, $message, $lineNumber, array $parameters = array())
    {
        $context->buildViolation($message)->shouldBeCalled()->willReturn($builder);

        $builder->atPath('lines['.$lineNumber.']')->shouldBeCalled()->willReturn($builder);

        foreach ($parameters as $name => $value) {
            $builder->setParameter($name, $value)->shouldBeCalled()->willReturn($builder);
        }

        $builder->addViolation()->shouldBeCalled();
    }

    public static function shouldNotReportAnyError($context)
    {
        $context->buildViolation(Argument::any())->shouldNotBeCalled();
    }
}
