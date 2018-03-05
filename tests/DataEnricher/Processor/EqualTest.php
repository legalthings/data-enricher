<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Equal
 */
class EqualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Equal;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Equal('<equal>');
    }
    
    public function instructionProvider()
    {
        return [
            [['string1', 'string1'], true],
            [['string1', 'string2'], false],
            [[true, true], true],
            [[false, false], true],
            [[true, false], false],
            [[['foo'], ['foo']], true],
            [[['foo'], ['bar']], false]
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
