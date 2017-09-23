<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * @covers LegalThings\DataEnricher\Processor\Transform
 */
class TransformTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor\merge;
     */
    protected $processor;

    public function setUp()
    {
        $this->processor = new Processor\Transform('<transformation>');
    }
    
    public function testApplyToNodeWithObjectForHash()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = 'test';
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['hash:sha256']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(hash('sha256', 'test'));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithRefNode()
    {
        $node = $this->createMock(Node::class);
        
        $refNode = $this->createMock(Node::class);
        $refNode->expects($this->atLeastOnce())
            ->method('getResult')
            ->willReturn('test');
        
        $node->input = $refNode;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['hash:sha256']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(hash('sha256', 'test'));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithObjectForTransformation()
    {
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'hash',
                    'args' => ['sha256', 'test']
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(hash('sha256', 'test'));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithHashHmac()
    {
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'hash_hmac',
                    'args' => ['sha256', 'data', 'secret']
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(hash_hmac('sha256', 'data', 'secret'));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeGeneratePrivateKey()
    {
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'generate_private_key',
                    'args' => []
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($this->callback(function($subject) {
                return $this->checkKey('private', $subject);
            }));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeGeneratePublicKey()
    {
        $privateKey = $this->getMockPrivateKey();
        $this->assertTrue($this->checkKey('private', $privateKey));
        
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'generate_public_key',
                    'args' => [$privateKey]
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($this->callback(function($subject) {
                return $this->checkKey('public', $subject);
            }));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodePrivateEncrypt()
    {
        $privateKey = $this->getMockPrivateKey();
        $this->assertTrue($this->checkKey('private', $privateKey));
        
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'private_encrypt',
                    'args' => ['my-data', $privateKey]
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($this->getPrivateEncryptedData());
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodePrivateDecrypt()
    {
        $publicKey = $this->getMockPublicKey();
        $this->assertTrue($this->checkKey('public', $publicKey));
        $data = $this->getPrivateEncryptedData();
        
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'private_decrypt',
                    'args' => [$data, $publicKey]
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with('my-data');

        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodePublicEncrypt()
    {
        $publicKey = $this->getMockPublicKey();
        $this->assertTrue($this->checkKey('public', $publicKey));
        
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'public_encrypt',
                    'args' => [$this->getMockPrivateKey(), $publicKey]
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($this->callback(function ($subject) {
                return strlen($subject) === strlen($this->getPublicEncryptedData());
            }));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeGenerateSignature()
    {
        $privateKey = $this->getMockPrivateKey();
        $this->assertTrue($this->checkKey('private', $privateKey));
        
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'generate_signature',
                    'args' => ['my-data', $privateKey]
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with($this->getPrivateSignedData());
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeVerifySignature()
    {
        $publicKey = $this->getMockPublicKey();
        $this->assertTrue($this->checkKey('public', $publicKey));
        $signature = $this->getPrivateSignedData();
        
        $node = $this->createMock(Node::class);
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn([
                (object)[
                    'function' => 'verify_signature',
                    'args' => ['my-data', $signature, $publicKey]
                ]
            ]);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(true);
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithObjectForStrToTimeConversion()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = '2017-01-01T22:00:00.000Z';
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['strtotime']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(strtotime('2017-01-01T22:00:00.000Z'));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithObjectForDateFormat()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = 1483228800;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['date:d-m-Y']);
        
        $node->expects($this->atLeastOnce())
            ->method('setResult')
            ->with(date('d-m-Y', 1483228800));
        
        $this->processor->applyToNode($node);
    }
    
    public function testApplyToNodeWithoutInput()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = null;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['hash:sha256']);
        
        $node->expects($this->never())
            ->method('setResult');
        
        $this->processor->applyToNode($node);
    }
    
    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown transformation 'unknown'
     */
    public function testApplyToNodeWithInvalidMethod()
    {
        $node = $this->createMock(Node::class);
        
        $node->input = 1483228800;
        
        $node->expects($this->atLeastOnce())
            ->method('getInstruction')
            ->with($this->processor)
            ->willReturn(['unknown']);
        
        $node->expects($this->never())
            ->method('setResult');
        
        $this->processor->applyToNode($node);
    }
    
    
    protected function checkKey($type, $key) {
        if (!preg_match('/^-----BEGIN (RSA |DSA )?' . strtoupper($type) . ' KEY-----/', $key)) {
            return false;
        }
        
        return true;
    }
    
    protected function getPrivateEncryptedData() {
        return 'bCqKxAsBapQO2KRNR1FD3eF1PDhisSBTqmVBXrSZ3UaFP/Fiuk8Ffze0KPEfJh3x5voV5E6yeOdNrbmhKS5rRfK4x8kvBWkSFSxi9v27YvsMd2Cou22x9Qn0WJy9gd/PzLm5x0BsjkEeQvvfGOhbaJm12QnVMj0dIuRc8EFfRBV18wbM1TJrkkTxSOtgxecTPG3Tt74Ow3SAXpw3OK55i4OId9q4fwSnOjS5AFDwo8wjDSxvQaQQ3ZqE+ygLN0wYo4mIdq6uCVsblfYG3PriQ89w003Q1LJui+LPxyCzNSddtwqgNJ28xP+9zNHTuo/cY1re5TL2FB7rKuOQc7jhnA==';
    }
    
    protected function getPublicEncryptedData() {
        return 'uYYF0IiRJYdZE7Iggjv9WAQBYgSsLf9BM4AoiYk3RtME/Bo84ym3jdIMDJXFq1Ku3yEJnAV6ypLNEkflWi9G53uHQjpZqb+3XACCLRHiW5/FUMqfpJvkno73oiIQWptf4e5OI83MNbsieJj/ucHwXUGOEuPFWr3mT+QpOuKivh/C6k6+xi/XDnw2dUU6ulDF+lMANHppRmKWddOuHl8XJe53JaGoC9oNO4cjDw2S7+BV2oV9fs8BkodwfZ/IR0fzh2w8ww+3NdJV3/Q5FKVTGisEqLE7Zl/0jmVdLRbOObhaiS6wHVVYkGiLvEoijXWrBt4zmE218YtUXdZseE0WgQ==';
    }
    
    protected function getPrivateSignedData() {
        return 'Ne4XVakL2y4dWtqFRw+r6Apo+QTZuUbUtOy1wHVidvZxAA7FxvLYNu1hsLR8EuvLvAGfkJjiLP3iy8ZCmnAtnXL5meF2m60i7WyzNyHTIJptjEirtZT6sgLnc/sVPNm5DZP53trSY3N9WtMsWpL19GwukLIbEmP/2v/p8bnBjS6rLlg8/Whlbp1iEfLWJ3jGQh1YAZ0/Jv1tUYbAsv8F8wtx5RaVF7GXe2NVW+Cexc5VqezdwZsNYwuKUdzzpuk3ZE8iZePqvu36UKn2pQl/sURlPn3AoyUOyB6s0YyEFornCEVduls3Z7qbb+Aiui1bt8OXZotx7borcsDqu5Jy2w==';
    }
    
    protected function getMockPrivateKey() {
        return
<<<KEY
-----BEGIN PRIVATE KEY-----
MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDXuWwxDa9hUiHL
8+Byu64lwXWfRhsszuyH6VJWcNb+YnVRmHKEvmx6bWwp/fL3f2zU1MbhcRDZ/b3V
dK2ntu3fDNcimAOsPd9n/Iywdr8meu86aXsY9UXIUMOAmZG9xrRTBLTrNoF4WI28
6c45H+sBHOvwQisW88gUhTqJo3DuZs4zV0W7J9BJzLbJz16VLpfNWqbaTC9IjZca
wlv6NiyVZ7z2PFMwSYLLHlE7ARoffMJtSD065WNyUfr/bx+r/aPtRp3acu1e/nPz
5+XUjJsrmZsatAoo0eF7Lio9Tr5t6Lopl2SJeFVQmKUOQQaHBDw6GQ6etHbNEw1L
SPTW2mwDAgMBAAECggEADou3KtuUXsiN9NWd5b6X8H3J85JjlLLjKgrmfWOga4iy
Bm10E1VPtTWZnH8+Gcgiy3sJafwYucotOa5jYzKneWnBO9jqLnwBi15xEhj/rJWP
ee3Q2g73xJkFha9VL2mtkQd5N372XeoCrKFLQXjQfMO5ePrNMv//FrshBlTZ8Ykn
WKsXp54Oj8FwiAJMzRVc/meg3UqXix4KrCx+p3Cu1OuPonw3Wo4gTkCyRLdzptMv
mkPjtd4kBvNrXGJ8yzv84F//o/HytJoQEBxRwyn8ixLqxt0rMBXpa3O3+11WlHbb
eAGV8SEGm1f/L7aA61os4sEOcUVL3EnSlos6FPJ/kQKBgQD0m/0oqVxSkDpJ18TL
btzI1ie79mQ13/qDLPfKQXjIfhXtHoBXkHY/ViIei5IaqZKqpwR0QxGE/Wm9mOL8
Sd8mXi6qomg7Jbm7/ocFKbLnvCSosloyMG3o2UeyWfnugRIZ8zF2aP8zSCGB81YH
6zQE6hGGMqdGS9BYuEfj/ah+2wKBgQDhxRe0hQmkM87GE4kbycsOEQ7tJ3xnnIFc
6Qf8m/x2ennPfhCSjK4UVipW3l/bPL7LC/xqJeRT4PtW2AtYhdFRrwVpOp1XabZ1
NhDhn8zMkwAMqdEfNiPPxP+zjYQWcH3g30QLDSA5IFsu8KVS9yqEiiNi+rHEBeJu
G6z4gczr+QKBgQCAUcSArDfuaBLr4fEu9z2DbjTx+dOgH5t/bPugcrA2HU7LUZDq
XrJpj3nepEFFE2gJdgx0ISrzpSzvdWC+ENKrggThJsmfHa9N27xhDPr5bk2c4dNH
OPiviix+d40RfUxNqLJt967I7DIHxet/w/dKDLCcwb3WtrbZk/LCu7LEuQKBgD7T
NraAMt+jnw2VkBjQfZXLf2jVDRRUA9eT7SoJSia6DD2GKhxt019LXvrPZMNAUyNz
kWkEZFZHDAntkrKugkIFvy66JeCWRTS2t9nhOv9OhLwBYXUa/roparJPVgtcgFrc
JjYhD+91pooYYxkEOKurOQ+pDWCr9oeHYgnBLQIZAoGAdw5h1BGFHPOY89EMPLjA
G7JG1dpW/b4GJPkcyrjPnu+oTZnV/UWcMX3Rof2P4iffAr5I/09bkc+a/GT7D4NL
/o8JymNzGNOn60UaoJlxx+VigvzX/2+qWV3ShTg2VhnA9QrwoBa7QrNUGGXYYO3C
pTkrZVXawk0H5sAP4QLyuoA=
-----END PRIVATE KEY-----
KEY;
    }

    protected function getMockPublicKey() {
        return
<<<KEY
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA17lsMQ2vYVIhy/Pgcruu
JcF1n0YbLM7sh+lSVnDW/mJ1UZhyhL5sem1sKf3y939s1NTG4XEQ2f291XStp7bt
3wzXIpgDrD3fZ/yMsHa/JnrvOml7GPVFyFDDgJmRvca0UwS06zaBeFiNvOnOOR/r
ARzr8EIrFvPIFIU6iaNw7mbOM1dFuyfQScy2yc9elS6XzVqm2kwvSI2XGsJb+jYs
lWe89jxTMEmCyx5ROwEaH3zCbUg9OuVjclH6/28fq/2j7Uad2nLtXv5z8+fl1Iyb
K5mbGrQKKNHhey4qPU6+bei6KZdkiXhVUJilDkEGhwQ8OhkOnrR2zRMNS0j01tps
AwIDAQAB
-----END PUBLIC KEY-----
KEY;
    }
}
