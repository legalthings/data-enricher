<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\IfElse
 */
class IfElseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\IfElse;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\IfElse('<if>');
    }
    
    public function instructionProvider()
    {
        return [
            [['condition' => true], 'foo', 'foo'],
            [['condition' => false], 'foo', null],
            [['condition' => true, 'then' => 'foo'], null, 'foo'],
            [['condition' => false, 'then' => 'foo'], null, null],
            [['condition' => true, 'then' => 'foo', 'else' => 'bar'], null, 'foo'],
            [['condition' => false, 'then' => 'foo', 'else' => 'bar'], null, 'bar']
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param $instruction
     * @param $input
     * @param $result
     */
    public function testApplyToNode($instruction, $input, $result)
    {
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn($instruction);
        
        $node->expects($this->any())
            ->method('getResult')
            ->willReturn($input);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($result);
        
        $this->processor->applyToNode($node);
    }
}
