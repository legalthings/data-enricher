<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Encode
 */
class EncodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Encode;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Encode('<encode>');
    }
    
    public function instructionProvider()
    {
        return [
            [['input' => 'foo', 'algo' => 'base64'], 'Zm9v'],
            [['input' => 'foo', 'algo' => 'base58'], 'bQbp'],
            [['input' => 'http://foo.example.com?bar=crux', 'algo' => 'url'], 'http%3A%2F%2Ffoo.example.com%3Fbar%3Dcrux']
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
