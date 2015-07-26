<?php

namespace Docbot\Test\Fixer;

use Symfony\CS\FixerInterface;

abstract class FixerTestCase extends \PHPUnit_Framework_TestCase
{
    /** @var FixerInterface */
    private $fixer;

    /** @return FixerInterface */
    abstract protected function createFixer();

    protected function setUp()
    {
        $this->fixer = $this->createFixer();
    }

    /** @dataProvider getExamples */
    public function testFix($input, $expected, $message, $file = null)
    {
        $this->assertEquals($expected, $this->fixer->fix(new \SplFileObject($file ?: __DIR__.'/../Fixtures/fixer.txt'), $input), $message);
    }

    /** @return array A list of tests containing: $input, $expected, $message[, $file] */
    abstract public function getExamples();
}
