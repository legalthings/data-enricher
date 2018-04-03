<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Serialize
 */
class SerializeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Serialize;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Serialize('<serialize>');
    }
    
    public function instructionProvider()
    {
        return [
            [['input' => ['foo' => 'bar', 'flag' => true], 'format' => 'json'], '{"foo":"bar","flag":true}'],
            [['input' => ['foo' => 'bar', 'flag' => true], 'format' => 'url'], 'foo=bar&flag=1']
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
