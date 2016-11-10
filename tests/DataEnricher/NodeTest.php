<?php

namespace LegalThings\DataEnricher;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Node
 */
class NodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor|MockObject;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = $this->createMock(Processor::class);
        $this->processor->expects($this->any())->method('getProperty')->willReturn('<process>');
    }
    
    public function testGetIntruction()
    {
        $node = new Node((object)[
            '<process>' => 'foo'
        ]);
        
        $instruction = $node->getInstruction($this->processor);
        $this->assertEquals('foo', $instruction);
    }
    
    public function testGetIntructionReference()
    {
        $refNode = $this->createMock(Node::class);
        $refNode->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn('foo');

        $node = new Node((object)[
            '<process>' => $refNode
        ]);
        
        $instruction = $node->getInstruction($this->processor);
        $this->assertEquals('foo', $instruction);
    }
    
    public function testGetIntructionReferenceTwice()
    {
        $refNode2 = $this->createMock(Node::class);
        $refNode2->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn('foo');

        $refNode1 = $this->createMock(Node::class);
        $refNode1->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($refNode2);

        $node = new Node((object)[
            '<process>' => $refNode1
        ]);
        
        $instruction = $node->getInstruction($this->processor);
        $this->assertEquals('foo', $instruction);
    }
    
    public function testGetIntructionDeepReference()
    {
        $refNode = $this->createMock(Node::class);
        $refNode->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn('foo');

        $node = new Node((object)[
            '<process>' => (object)['value' => $refNode]
        ]);
        
        $instruction = $node->getInstruction($this->processor);
        $this->assertEquals((object)['value' => 'foo'], $instruction);
    }
    
    public function testGetIntructionDeepReferenceTwice()
    {
        $refNode2 = $this->createMock(Node::class);
        $refNode2->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn('foo');

        $refNode1 = $this->createMock(Node::class);
        $refNode1->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn(['cool' => $refNode2]);

        $node = new Node((object)[
            '<process>' => (object)['value' => $refNode1]
        ]);
        
        $instruction = $node->getInstruction($this->processor);
        $this->assertEquals((object)['value' => ['cool' => 'foo']], $instruction);
    }
}
