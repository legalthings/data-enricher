<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\JmesPath
 */
class TransformTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyToNodeWithObjectForHash()
    {
        $processor = new Processor\Transform('<transformation>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        
        $node->input = 'test';
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn(['hash:sha256']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(hash('sha256', 'test'));
        
        $processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithObjectForStrToTimeConversion()
    {
        $processor = new Processor\Transform('<transformation>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        
        $node->input = '2017-01-01T22:00:00.000Z';
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn(['strtotime']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(strtotime('2017-01-01T22:00:00.000Z'));
        
        $processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithObjectForDateFormat()
    {
        $processor = new Processor\Transform('<transformation>');
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        
        $node->input = 1483228800;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn(['date:d-m-Y']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(date('d-m-Y', 1483228800));
        
        $processor->applyToNode($node);
    }
}