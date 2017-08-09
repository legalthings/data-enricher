<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Merge
 */
class MergeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\merge;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Merge('<merge>');
    }
    
    public function instructionProvider()
    {
        return [
            [
                [['foo' => 'red', 'bar' => 'Sir'], (object)['bird' => 'duck', 'mammal' => 'monkey']],
                (object)['foo' => 'red', 'bar' => 'Sir', 'bird' => 'duck', 'mammal' => 'monkey']
            ],
            [
                [['red', 'Sir'], ['duck', 'monkey']],
                ['red', 'Sir', 'duck', 'monkey']
            ],
            [
                ['dark', 'moon'],
                'darkmoon'
            ],
            [
                ['foo', 'bar', null],
                'foobar'
            ],
            [
                [null, null],
                null
            ],
            [
                [],
                null
            ],
            [
                [
                    new \ArrayIterator([
                        'foo' => 'red',
                        'bar' => 'Sir'
                    ]),            
                    [
                       'bird' => 'duck',
                        'mammal' => 'monkey'
                    ]
                ],
                (object)['foo' => 'red', 'bar' => 'Sir', 'bird' => 'duck', 'mammal' => 'monkey']
            ]
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param array $instructions
     * @param mixed $result
     */
    public function testApplyToNode(array $instructions, $result)
    {
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn($instructions);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($result);
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithRefNode()
    {
        $refNode = $this->createMock(Node::class);
        
        $data = [
            (object) [
                'foo' => 'red',
                'bar' => 'Sir'
            ],            
            (object)[
               'bird' => 'duck',
                'mammal' => 'monkey'
            ]
        ];
        
        $refNode->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($data);
        
        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn($refNode);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with((object)['foo' => 'red', 'bar' => 'Sir', 'bird' => 'duck', 'mammal' => 'monkey']);
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithRefAndArrayNode()
    {
        $refNode = $this->createMock(Node::class);
        
        $data = new \stdClass;
        $data->foo = 'red';
        $data->bar = 'Sir';
        
        $refNode->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($data);
        
        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
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
        
        $this->processor->applyToNode($node);
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to apply <merge> processing instruction: Expected an array, got a string
     */
    public function testInvalidInstructions()
    {
        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn('not an array');
        
        $this->processor->applyToNode($node);
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to apply <merge> processing instruction: Mixture of scalar and non-scalar values
     */
    public function testMixedValues()
    {
        $first = ['red', 'Sir'];
        $second = 'a scalar value';

        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([$first, $second]);
        
        $this->processor->applyToNode($node);
    }
}
