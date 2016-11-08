<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Enrich
 */
class MathTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Math;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Enrich('<enrich>');
    }
    
    
    public function instructionProvider()
    {
        return [
            [
                (object)[
                    'extra' => [['email' => 'john@example.com', 'gender' => 'male'], ['email' => 'jane@example.com', 'gender' => 'female']],
                    'match' => 'email'
                ],
                [['email' => 'john@example.com', 'name' => 'John Doe'], ['email' => 'jane@example.com', 'name' => 'Jane Doe']],
                [['email' => 'john@example.com', 'name' => 'John Doe', 'gender' => 'male'], ['email' => 'jane@example.com', 'name' => 'Jane Doe', 'gender' => 'female']]
            ],
            [
                (object)[
                    'extra' => [['user' => ['email' => 'john@example.com', 'gender' => 'male']], ['user' => ['email' => 'jane@example.com', 'gender' => 'female']]],
                    'match' => [
                        'extra' => 'user.email',
                        'source' => 'email'
                    ],
                    'select' => 'user' // @todo
                ],
                [['email' => 'john@example.com', 'name' => 'John Doe'], ['email' => 'jane@example.com', 'name' => 'Jane Doe']],
                [['email' => 'john@example.com', 'name' => 'John Doe', 'gender' => 'male'], ['email' => 'jane@example.com', 'name' => 'Jane Doe', 'gender' => 'female']]
            ]
        ];
    }
    
    /**
     * @dataProvider instructionProvider
     * 
     * @param object $instruction
     * @param array $source
     * @param float $result
     */
    public function testApplyToNode($instruction, array $source, $result)
    {
        $node = $this->createMock(Node::class);

        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn($instruction);

        $node->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn($source);

        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($result);

        $this->processor->applyToNode($node);
    }
}
