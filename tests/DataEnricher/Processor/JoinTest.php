<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Join
 */
class JoinTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\join;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Join('<join>');
    }
    
    public function instructionProvider()
    {
        return [
            [
                ['input' => ['dark', 'moon']],
                'darkmoon'
            ],
            [
                ['input' => ['dark', 'moon'], 'glue' => ' '],
                'dark moon'
            ],
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
}
