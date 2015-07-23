<?php

namespace WouterJ\Docbot\Test\Reviewer;

use Docbot\Docbot;
use Docbot\ServiceContainer\ContainerFactory;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
abstract class BaseReviewerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Docbot */
    protected $linter;

    protected function setUp()
    {
        $container = (new ContainerFactory())->createContainer();
        $this->linter = $container->get('docbot');
    }

    /** @dataProvider getExamples */
    public function testLinting($file, array $expectedViolations, $message = null)
    {
        $violations = $this->linter->lint($file);

        $this->assertViolations($expectedViolations, $violations, $message);
    }

    abstract protected function getReviewerClass();

    abstract public function getExamples();

    protected function assertViolations(array $violations, ConstraintViolationListInterface $violationList, $message = null)
    {
        $message = $message ? PHP_EOL.PHP_EOL.$message : $message;

        foreach ($violationList as $actual) {
            if (get_class($actual->getConstraint()) !== $this->getReviewerClass()) {
                continue;
            }

            $matched = false;
            foreach ($violations as $key => $expected) {
                if (
                    $expected->getMessage() === $actual->getMessage()
                    || $expected->getPropertyPath() === $actual->getPropertyPath()
                ) {
                    unset($violations[$key]);
                    $matched = true;
                    continue 2;
                }
            }

            if (!$matched) {
                $this->fail('Violation "'.$actual->getMessage().'" on line "'.$actual->getPropertyPath().'" was not expected."'.$message);
            }
        }

        if (!empty($violations)) {
            $this->fail(
                "These violations were expected, but not found: \n"
                .implode("\n", array_map(function ($v) {
                    return $v->getMessage().': '.$v->getPropertyPath();
                }, $violations))
                .$message
            );
        }
    }

    protected function getViolationProphet($message, $line)
    {
        $violation = $this->prophesize(ConstraintViolation::class);

        $violation->getMessage()->willReturn($message);
        $violation->getPropertyPath()->willReturn('line['.$line.']');

        return $violation->reveal();
    }
}
