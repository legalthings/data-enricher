<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Merge
 */
class MergeTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyToNodeWithObjects()
    {
        $first = ['foo' => 'red', 'bar' => 'Sir'];
        $second = (object)['bird' => 'duck', 'mammal' => 'monkey'];

        $processor = new Processor\Merge('<merge>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn([$first, $second]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with((object)['foo' => 'red', 'bar' => 'Sir', 'bird' => 'duck', 'mammal' => 'monkey']);
        
        $processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithArrays()
    {
        $first = ['red', 'Sir'];
        $second = ['duck', 'monkey'];

        $processor = new Processor\Merge('<merge>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn([$first, $second]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(['red', 'Sir', 'duck', 'monkey']);
        
        $processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithStrings()
    {
        $first = 'dark';
        $second = 'moon';

        $processor = new Processor\Merge('<merge>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn([$first, $second]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with('darkmoon');
        
        $processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithRefNode()
    {
        $refNode = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        
        $data = new \stdClass;
        $data->foo = 'red';
        $data->bar = 'Sir';
        
        $refNode->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($data);
        
        $processor = new Processor\Merge('<merge>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn([
                $refNode,
                (object)[
                    'bird' => 'duck',
                    'mammal' => 'monkey'
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with((object)['foo' => 'red', 'bar' => 'Sir', 'bird' => 'duck', 'mammal' => 'monkey']);
        
        $processor->applyToNode($node);
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to apply <merge> processing instruction: Expected an array, got a string
     */
    public function testInvalidInstructions()
    {
        $processor = new Processor\Merge('<merge>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn('not an array');
        
        $processor->applyToNode($node);
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to apply <merge> processing instruction: Mixture of scalar and non-scalar values
     */
    public function testMixedValues()
    {
        $first = ['red', 'Sir'];
        $second = 'a scalar value';

        $processor = new Processor\Merge('<merge>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn([$first, $second]);
        
        $processor->applyToNode($node);
    }
}
