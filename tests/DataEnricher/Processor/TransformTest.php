<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Transform
 */
class TransformTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\merge;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Transform('<transformation>');
    }
    
    public function testApplyToNodeWithObjectForHash()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = 'test';
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['hash:sha256']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(hash('sha256', 'test'));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithRefNode()
    {
        $node = $this->createMock(Node::class);
        
        $refNode = $this->createMock(Node::class);
        $refNode->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn('test');
        
        $node->input = $refNode;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['hash:sha256']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(hash('sha256', 'test'));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithObjectForStrToTimeConversion()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = '2017-01-01T22:00:00.000Z';
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['strtotime']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(strtotime('2017-01-01T22:00:00.000Z'));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithObjectForDateFormat()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = 1483228800;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['date:d-m-Y']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(date('d-m-Y', 1483228800));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithoutInput()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = null;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['hash:sha256']);
        
        $node->expects($this->never())
            ->method('setResult');
        
        $this->processor->applyToNode($node);
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown transformation 'unknown'
     */
    public function testApplyToNodeWithInvalidMethod()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = 1483228800;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['unknown']);
        
        $node->expects($this->never())
            ->method('setResult');
        
        $this->processor->applyToNode($node);
    }
}