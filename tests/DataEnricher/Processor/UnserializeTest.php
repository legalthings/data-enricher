<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Unserialize
 */
class UnserializeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Unserialize;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Unserialize('<unserialize>');
    }
    
    public function instructionProvider()
    {
        return [
            [['input' => '{"foo":"bar","flag":true}', 'format' => 'json'], (object)['foo' => 'bar', 'flag' => true]],
            [['input' => 'foo=bar&flag=1', 'format' => 'url'], (object)['foo' => 'bar', 'flag' => true]]
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
