<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Reference
 */
class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyToNode()
    {
        // for bc
        $processor = new Processor\Reference('<ref>');
        
        $node = $this->createMock(Node::class);
        
        $data = new \stdClass;
        $data->foo = (object)['bar' => 'crux'];
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn('foo.bar');
        
        $node->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($data);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with('crux');
        
        $processor->applyToNode($node);
    }
    
    public function testApplyToNodeAsJmesPath()
    {
        $processor = new Processor\Reference('<ref>');
        
        $node = $this->createMock(Node::class);
        
        $data = new \stdClass;
        $data->foo = (object)['bar' => 'crux'];
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn("foo.bar=='crux'");
        
        $node->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($data);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(true);
        
        $processor->applyToNode($node);
    }
}
