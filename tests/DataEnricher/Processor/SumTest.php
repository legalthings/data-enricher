<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Sum
 */
class SumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Sum;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Sum('<sum>');
    }
    
    public function instructionProvider()
    {
        return [
            [
                [1, 2, 3],
                6
            ],
            [
                [1.0, 2.0, 3.0],
                6.0
            ],
            [
                [1.1, 2.2, 3.3],
                6.6
            ]
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param array $instructions
     * @param mixed $result
     */
    public function testApplyToNode(array $instructions, $result)
    {
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn($instructions);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($result);
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithRefNode()
    {
        $refNode = $this->createMock(Node::class);
        
        $refNode->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn([1, 2, 3]);
        
        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn($refNode);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(6);
        
        $this->processor->applyToNode($node);
    }
}
