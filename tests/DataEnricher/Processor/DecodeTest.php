<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Decode
 */
class DecodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Decode;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Decode('<decode>');
    }
    
    public function instructionProvider()
    {
        return [
            [['input' => 'Zm9v', 'algo' => 'base64'], 'foo'],
            [['input' => 'bQbp', 'algo' => 'base58'], 'foo'],
            [['input' => 'http%3A%2F%2Ffoo.example.com%3Fbar%3Dcrux', 'algo' => 'url'], 'http://foo.example.com?bar=crux']
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
