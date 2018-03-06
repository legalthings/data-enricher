<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Replace
 */
class ReplaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Replace;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Replace('<replace>');
    }
    
    public function instructionProvider()
    {
        return [
            [['input' => 'foo/bar/crux', 'find' => 'crux', 'replacement' => 'dog'], 'foo/bar/dog'],
            [['input' => 'foo/bar/crux', 'find' => 'something', 'replacement' => 'dog'], 'foo/bar/crux'],
            [['input' => 'foo/bar/crux', 'regex' => '/^foo/', 'replacement' => 'dog'], 'dog/bar/crux'],
            [['input' => 'foo/bar/crux', 'regex' => '/^bar/', 'replacement' => 'dog'], 'foo/bar/crux']
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
