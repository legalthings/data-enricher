<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Match
 */
class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Match;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Match('<match>');
    }
    
    public function instructionProvider()
    {
        return [
            [['input' => 'foo/bar/crux', 'find' => 'crux'], true],
            [['input' => 'foo/bar/', 'find' => 'crux'], false],
            [['input' => 'foo/bar/crux', 'regex' => '/^foo/'], true],
            [['input' => 'bar/crux', 'regex' => '/^foo/'], false]
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
