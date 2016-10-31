<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Math
 */
class MathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Math;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Math('<math>');
    }
    
    
    public function instructionProvider()
    {
        return [
            [
                '(3 * 4) + 2',
                [],
                14.0
            ],
            [
                'cos(0) * avg(0, 1, 2, 3)',
                [],
                1.5
            ],
            [
                'AMOUNT * 100',
                ['AMOUNT' => 75],
                7500
            ],
            [
                'amount * pow(1 + (interest / 100), years)',
                ['amount' => 200, 'interest' => '10%', 'years' => 5],
                322.102
            ]
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param string $expression
     * @param array  $variables
     * @param float  $result
     */
    public function testApplyToNode($expression, array $variables, $result)
    {
        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn($expression);

        $node->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($variables);

        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($result);

        $this->processor->applyToNode($node);
    }
    
    /**
     * @expectedException Hoa\Compiler\Exception\UnrecognizedToken
     */
    public function testApplyToNodeInvalidExpression()
    {
        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn('foo (= bar');
        
        $this->processor->applyToNode($node);
    }
    
    /**
     * @expectedException Hoa\Math\Exception\UnknownVariable
     */
    public function testApplyToNodeMissingVariable()
    {
        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn('foo + 10');
        
        $node->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn([]);
        
        $this->processor->applyToNode($node);
    }
}
