<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\JmesPath
 */
class DateFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Enrich;
     */
    protected $processor;
    
    public function setUp() 
    {
        $this->processor = new Processor\DateFormat('<dateformat>');
    }
    
    public function instructionProvider()
    {
        return [
            [
                (object)[
                    'date' => '@1501106400',
                    'format' => 'Y-m-d'
                ],
                '2017-07-26'
            ],
            [
                (object)[
                    'date' => '26-07-2017',
                    'format' => 'Y-m-d H:i:s'
                ],
                '2017-07-26 00:00:00'
            ],
            [
                (object)[
                    'date' => '26-07-2017',

                    'format' => 'c',
                    'timezone' => 'UTC'
                ],
                '2017-07-26T00:00:00+00:00'
            ],
            [
                (object)[
                    'date' => '2017-07-27T00:00:00+0200',
                    'format' => 'Y-m-d'
                ],
                '2017-07-27'
            ]
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param object $instruction
     * @param string $result
     */
    public function testApplyToNode($instruction, $result)
    {        
        $node = $this->getMockBuilder(Node::class)
            ->disableOriginalConstructor()
            ->disableProxyingToOriginalMethods()
            ->getMock();
        
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

