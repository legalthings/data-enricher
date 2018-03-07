<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\NumberFormat
 */
class NumberFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\join;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\NumberFormat('<numberformat>');
    }
    
    public function instructionProvider()
    {
        return [
            [
                ['input' => 123456789.01234],
                '123,456,789.012'
            ],
            [
                ['input' => 123456789.01234, 'decimals' => 2],
                '123,456,789.01'
            ],
            [
                ['input' => 123456789.01234, 'locale' => 'nl_NL'],
                '123.456.789,012'
            ],
            [
                ['input' => 123456789.01234, 'currency' => 'EUR'],
                '€123,456,789.01'
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
