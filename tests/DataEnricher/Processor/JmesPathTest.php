<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\JmesPath
 */
class JmesPathTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyToNodeWithObject()
    {
        // for bc
        $processor = new Processor\JmesPath('<jmespath>');
        
        $node = $this->createMock(Node::class);
        
        $data = new \stdClass;
        $data->foo = 'red';
        $data->bar = 'Sir';
        $data->qux = 10;
        $data->baz = 2;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn('{foo: foo, title: bar, calc: (qux * baz) + 5}');
        
        $node->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($data);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with((object)['foo' => 'red', 'title' => 'Sir', 'calc' => 25]);
        
        $processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithInputAndQuery()
    {
        $processor = new Processor\JmesPath('<apply>');
        
        $node = $this->createMock(Node::class);
        
        $data = new \stdClass;
        $data->foo = 'red';
        $data->bar = 'Sir';
        $data->qux = 10;
        $data->baz = 2;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn([
                'input' => $data,
                'query' => '{foo: foo, title: bar, calc: (qux * baz) + 5}'
            ]);
        
        $node->expects($this->never())
            ->method('getResult');
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with((object)['foo' => 'red', 'title' => 'Sir', 'calc' => 25]);
        
        $processor->applyToNode($node);
    }
}
