<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Evaluate
 */
class EvaluateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Reference;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Evaluate('<eval>');
    }

    public function instructionProvider()
    {
        return [
            [
                "foo.bar == 'test'",
                (object)['foo' => (object)['bar' => 'test']],
                true
            ],
            [
                "foo.bar == 'test' && baz.zoo[0] == 'rest'",
                (object)[
                    'foo' => (object)['bar' => 'test'],
                    'baz' => (object)['zoo' => ['rest']]
                ],
                true
            ],
            [
                "foo.bar == 'test' || baz.zoo[0] == 'rest'",
                (object)[
                    'foo' => (object)['bar' => 'test'],
                    'baz' => (object)['zoo' => [null]]
                ],
                true
            ],
            [
                "foo.bar == null",
                (object)['foo' => (object)['bar' => null]],
                true
            ],
            [
                "foo.bar == 'tests'",
                (object)['foo' => (object)['bar' => 'test']],
                false
            ],
            [
                "foo.bar == 'test' && baz.zoo[0] == 'rest'",
                (object)[
                    'foo' => (object)['bar' => 'test'],
                    'baz' => (object)['zoo' => ['rests']]
                ],
                false
            ],
        ];
    }

    /**
     * @dataProvider instructionProvider
     *
     * @param string|object|array $instruction
     * @param object              $source
     * @param string|object|array $result
     */
    public function testApplyToNode($instruction, $source, $result)
    {
        $processor = $this->processor->withSourceAndTarget($source, []);

        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn($instruction);

        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($result);

        $processor->applyToNode($node);
    }
}
