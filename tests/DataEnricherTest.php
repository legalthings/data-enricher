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
    public function testApplyToWithSelf()
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
    public function testApplyToWithtarget()
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
    public function testConstructWithString()
    {
        $this->enricher->applyTo('foo');
    }
    
    /**
     * Test object construction with an array
     * 
     * @expectedException Exception
     * @expectedExceptionMessage Data enricher on works on an object, not on a array
     */
    public function testConstructWithArray()
    {
        $this->enricher->applyTo(['foo' => 'bar']);
    }
    
    public function testContructDefaultProcessor()
    {
        $this->enricher = new DataEnricher();
        foreach($this->enricher->processors as $processor) {
            switch ($processor->getProperty()) {
                case '<ifset>':
                    $this->assertInstanceOf(Processor\IfSet::class, $processor);
                    break;
                case '<ref>':
                case '_ref':
                    $this->assertInstanceOf(Processor\Reference::class, $processor);
                    break;
                case '<switch>':
                case '_switch':
                    $this->assertInstanceOf(Processor\SwitchChoose::class, $processor);
                    break;
                case '<merge>':
                case '_merge':
                    $this->assertInstanceOf(Processor\Merge::class, $processor);
                    break;
                case '<tpl>':
                case '_tpl':
                    $this->assertInstanceOf(Processor\Mustache::class, $processor);
                    break;                    
                case '<src>':
                case '_src':
                    $this->assertInstanceOf(Processor\Http::class, $processor);
                    break;
                case '<apply>':
                case '<jmespath>':
                case '_jmespath':
                    $this->assertInstanceOf(Processor\JmesPath::class, $processor);
                    break;
                case '<transformation>':
                case '_transformation':
                    $this->assertInstanceOf(Processor\Transform::class, $processor);
                    break;
                case '<math>':
                    $this->assertInstanceOf(Processor\Math::class, $processor);
                    break;
                case '<enrich>':
                    $this->assertInstanceOf(Processor\Enrich::class, $processor);
                    break;
                case '<dateformat>':
                    $this->assertInstanceOf(Processor\DateFormat::class, $processor);
                    break;
                case '<equal>':
                    $this->assertInstanceOf(Processor\Equal::class, $processor);
                    break;
                case '<match>':
                    $this->assertInstanceOf(Processor\Match::class, $processor);
                    break;
                case '<if>':
                    $this->assertInstanceOf(Processor\IfElse::class, $processor);
                    break;
            }
        }
    }
    
    public function testApplyToIntegration()
    {
        $json = file_get_contents('./tests/_data/example.json');
        $object = json_decode($json);
        
        $enricher = new DataEnricher();
        $enricher->applyTo($object);
        
        $expected = (object) [
            'foo' => (object) [
              'bar' => (object) [
                'qux' => 12345,
              ],
              'term' => 'data enrichment',
              'city' => 'Amsterdam',
              'country' => 'Netherlands',
            ],
            'amount' => 12345,
            'message' => 'I want to go to Amsterdam, Netherlands',
            'shipping' => 'PostNL',
            'profile' => (object) [
              'qux' => 12345,
              'apples' => 100,
              'pears' => 220,
            ]
        ];
        
        $this->assertEquals($expected, $object);
    }
}
