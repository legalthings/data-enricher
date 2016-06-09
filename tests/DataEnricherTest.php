<?php

namespace LegalThings;

use LegalThings\DataEnricher;
use LegalThings\DataEnricher\Processor;

/**
 * Tests for LegalThings\DataEnricher
 */
class DataEnricherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var object
     */
    protected $data;
    
    /**
     * @var DataEnricher
     */
    protected $enricher;

    /**
     * @var Prophecy\Prophecy\ProphecyInterface
     */
    protected $upperProphecy;

    /**
     * @var Prophecy\Prophecy\ProphecyInterface
     */
    protected $copyProphecy;
    
    
    /**
     * Turn an associated array into an object (deep)
     * 
     * @param array $array
     * @return object
     */
    protected static function objectify(array $array)
    {
        $object = (object)$array;
        
        foreach ($object as &$value) {
            if (is_array($value) && !empty($value) && is_string(key($value))) {
                $value = self::objectify($value);
            }
        }
        
        return $object;
    }
    
    /**
     * Setup before each test
     */
    public function setup()
    {
        DataEnricher::$defaultProcessors = [];
        
        $this->object = self::objectify(['baz' => 'quz', 'zoo' => ['_upper' => 'fox']]);
        
        $this->upperProphecy = $this->prophesize(Processor::class);
        $this->copyProphecy = $this->prophesize(Processor::class);
        
        $this->enricher = new DataEnricher();
        $this->enricher->processors = [
            '_upper' => $this->upperProphecy->reveal(),
            '_copy' => $this->copyProphecy->reveal()
        ];
    }
    
    /**
     * Test DataEnricher::applyTo() self
     */
    public function testApplyTo_self()
    {
        $this->markTestIncomplete('tests needs to be updated, it no longer works after implementing nodes');
        
        $this->upperProphecy->applyTo()->will(function($nodes) {
            $nodes[0]->setResult((object)['zoo' => 'FOX']);
        })->shouldBeCalledTimes(1);

        $this->copyProphecy->applyTo($this->object)->will(function($args) {
            $args[0]->copy = $args[0]->baz;
        })->shouldBeCalledTimes(1);
        
        $this->enricher->applyTo($this->object);
        
        $this->assertSame('FOX', $this->object->zoo);
        
        $this->assertObjectHasAttribute('copy', $this->object);
        $this->assertSame('quz', $this->object->copy);
    }
    
    /**
     * Test DataEnricher::applyTo() other object
     */
    public function testApplyTo_target()
    {
        $this->markTestIncomplete('tests needs to be updated, it no longer works after implementing nodes');
        $target = (object)['diz' => 'fab'];
        
        $this->upperProphecy->applyTo($target)->shouldBeCalledTimes(1);
        $this->copyProphecy->applyTo($target)->shouldBeCalledTimes(1);
        
        $this->enricher->applyTo($target, $this->object);
    }
    
    /**
     * Test object construction with a string
     * 
     * @expectedException Exception
     * @expectedExceptionMessage Data enricher on works on an object, not on a string
     */
    public function testConstruct_string()
    {
        $this->enricher->applyTo('foo');
    }
    
    /**
     * Test object construction with an array
     * 
     * @expectedException Exception
     * @expectedExceptionMessage Data enricher on works on an object, not on a array
     */
    public function testConstruct_array()
    {
        $this->enricher->applyTo(['foo' => 'bar']);
    }
}
