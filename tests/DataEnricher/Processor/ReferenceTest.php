<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Reference
 */
class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Reference;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Reference('<ref>');
    }
    
    public function instructionProvider()
    {
        return [
            [
                'foo',
                (object)['foo' => 'bar'],
                [],
                'bar'
            ],
            [
                'foo.bar',
                (object)['foo' => ['bar' => 'crux']],
                [],
                'crux'
            ],
            [
                '$.foo.bar',
                (object)['foo' => ['bar' => 'crux']],
                [],
                'crux'
            ]
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param string|object|array $instruction
     * @param object              $source
     * @param array|object        $target
     * @param string|object|array $result
     */
    public function testApplyToNode($instruction, $source, $target, $result)
    {
        $processor = $this->processor->withSourceAndTarget($source, $target);
        
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
