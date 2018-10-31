<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\SwitchChoose
 */
class SwitchChooseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\SwitchChoose;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\SwitchChoose('<switch>');
    }
    
    public function instructionProvider()
    {
        return [
//            [
//                [
//                    'on' => 'foo',
//                    'options' => [
//                        'foo' => 'first',
//                        'bar' => 'second',
//                        'crux' => 'third'
//                    ],
//                    "default" => 'fourth'
//                ],
//                'first'
//            ],
//            [
//                [
//                    'on' => 'bar',
//                    'options' => [
//                        'foo' => 'first',
//                        'bar' => 'second',
//                        'crux' => 'third'
//                    ]
//                ],
//                'second'
//            ],
//            [
//                [
//                    'on' => 'something',
//                    'options' => [
//                        'foo' => 'first',
//                        'bar' => 'second',
//                        'crux' => 'third'
//                    ],
//                    'default' => 'fourth'
//                ],
//                'fourth'
//            ],
//            [
//                [
//                    'on' => 'something',
//                    'options' => [
//                        'foo' => 'first',
//                        'bar' => 'second',
//                        'crux' => 'third'
//                    ]
//                ],
//                null
//            ],
            [
                [
                    'on' => '30',
                    'options' => [
                        '30' => 'first',
                        '40' => 'second',
                        '50' => 'third'
                    ],
                    'default' => '60'
                ],
                'first'
            ],
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param $instruction
     * @param $result
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
