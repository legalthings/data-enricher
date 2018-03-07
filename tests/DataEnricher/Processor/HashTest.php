<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Hash
 */
class HashTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\Hash;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Hash('<hash>');
    }
    
    public function instructionProvider()
    {
        return [
            [['input' => 'foo', 'algo' => 'md5'], 'acbd18db4cc2f85cedef654fccc4a4d8'],
            [['input' => 'foo', 'algo' => 'md5', 'hmac' => 'bar'], '31b6db9e5eb4addb42f1a6ca07367adc'],
            [['input' => 'foo', 'algo' => 'sha1'], '0beec7b5ea3f0fdbc95d0dd47f3c5bc275da8a33'],
            [['input' => 'foo', 'algo' => 'sha1', 'hmac' => 'bar'], '85d155c55ed286a300bd1cf124de08d87e914f3a'],
            [['input' => 'foo', 'algo' => 'sha256'], '2c26b46b68ffc68ff99b453c1d30413413422d706483bfa0f98a5e886266e7ae'],
            [['input' => 'foo', 'algo' => 'sha256', 'hmac' => 'bar'], '147933218aaabc0b8b10a2b3a5c34684c8d94341bcf10a4736dc7270f7741851'],
            [['input' => 'foo', 'algo' => 'sha512'], 'f7fbba6e0636f890e56fbbf3283e524c6fa3204ae298382d624741d0dc6638326e282c41be5e4254d8820772c5518a2c5a8c0c7f7eda19594a7eb539453e1ed7'],
            [['input' => 'foo', 'algo' => 'sha512', 'hmac' => 'bar'], '24257d7210582a65c731ec55159c8184cc24c02489453e58587f71f44c23a2d61b4b72154a89d17b2d49448a8452ea066f4fc56a2bcead45c088572ffccdb3d8'],
            [['input' => 'foo', 'algo' => 'crc32'], -1938594527],
            [['input' => 'foo', 'algo' => 'crc32', 'hmac' => 'bar'], -1938594527] // doesn't support hmac variant
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
