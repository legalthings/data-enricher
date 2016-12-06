<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Mustache
 */
class MustacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Mustache;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Mustache('<tpl>');
    }
    
    
    public function instructionProvider()
    {
        return [
            [
                'hello {{ name }}',
                (object)['name' => 'world'],
                [],
                'hello world'
            ],
            [
                ['{{ today }}', '{{ tomorrow }}'],
                (object)['today' => 'monday', 'tomorrow' => 'tuesday'],
                [],
                ['monday', 'tuesday']
            ],
            [
                (object)['{{ key }}' => '{{ value }}'],
                (object)['key' => 'name', 'value' => 'John Doe'],
                [],
                (object)['name' => 'John Doe']
            ],
            [
                (object)[
                    '{{ key }}' => (object)[
                        '{{ nested_key }}' => '{{ nested_value }}'
                    ],
                    'ignore_this' => 'okay'
                ],
                (object)['key' => 'organization', 'nested_key' => 'name', 'nested_value' => 'Acme'],
                [],
                (object)[
                    'organization' => (object)[
                        'name' => 'Acme'
                    ],
                    'ignore_this' => 'okay'
                ]
            ]
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
    public function testApplyToNode($instruction, $source, $target, $result)
    {
        $processor = $this->processor->withSourceAndTarget($source, $target);
        
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($processor)
            ->willReturn($instruction);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($result);
        
        $processor->applyToNode($node);
    }
}
