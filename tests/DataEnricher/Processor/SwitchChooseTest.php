<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\SwitchChoose
 */
class SwitchChooseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\SwitchChoose;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\SwitchChoose('<switch>');
    }
    
    public function instructionProvider()
    {
        return [
            [
                ['on' => 'foo', 'options' => [
                    'foo' => 'first',
                    'bar' => 'second',
                    'crux' => 'third'
                ]],
                'first'
            ],
            [
                ['on' => 'bar', 'options' => [
                    'foo' => 'first',
                    'bar' => 'second',
                    'crux' => 'third'
                ]],
                'second'
            ],
            [
                ['on' => 'something', 'options' => [
                    'foo' => 'first',
                    'bar' => 'second',
                    'crux' => 'third'
                ]],
                null
            ],
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param $instruction
     * @param $result
     */
    public function testApplyToNode($instruction, $result)
    {
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn($instruction);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($result);
        
        $this->processor->applyToNode($node);
    }
}
