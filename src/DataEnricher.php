<?php

namespace LegalThings;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;

/**
 * Enrich objects by processing special properties.
 */
class DataEnricher
{
    /**
     * Default processors
     * @var array
     */
    public static $defaultProcessors = [
        '_ref' => Processor\Reference::class,
        '_switch' => Processor\SwitchChoose::class,
        '_src' => Processor\Http::class,
        '_merge' => Processor\Merge::class,
        '_jmespath' => Processor\JmesPath::class,
        '_tpl' => Processor\Mustache::class,
        '_transformation' => Processor\Transform::class
    ];
    
    
    /**
     * @var object
     */
    protected $source;
    
    /**
     * Processors, applied in specified order.
     * 
     * @var DataEnricher\Processor[]
     */
    public $processors;
    
    
    /**
     * Class constructor
     * 
     * @param object $source  Data source
     */
    public function __construct($source)
    {
        if (!is_object($source)) {
            throw new \Exception("Data enricher on works on an object, not on a " . gettype($source));
        }
        
        $this->source = $source;
        
        foreach (static::$defaultProcessors as $property => $processor) {
            if (is_string($processor)) {
                $class = $processor;
                $processor = new $class($this, $property);
            }
            
            $this->processors[] = $processor;
        }
    }
    
    /**
     * Get the source object
     * 
     * @return object
     */
    public function getSource()
    {
        return $this->source;
    }
    
    
    /**
     * Invoke enricher 
     * 
     * @param array|object|string $target  Target or dot key path
     */
    public function applyTo($target)
    {
        if (is_string($target)) {
            $target = \DotKey::on($this->source)->get($target);
        }
        
        $nodes = $this->findNodes($target);
        
        foreach ($this->processors as $processor) {
            $processor->applyTo($nodes);
        }
        
        $this->applyNodeResults($target);
    }

    /**
     * Find nodes that have processing instructions
     * 
     * @param array|object $target
     * @return array
     */
    public function findNodes(&$target)
    {
        $nodes = [];
        
        foreach ($target as $key => &$value) {
            if (is_array($value) || (is_object($value) && !$value instanceof Node)) {
                $nodes = array_merge($nodes, $this->findNodes($value));
            }
            
            if ($value instanceof \stdClass && $this->hasProcessorProperty($value)) {
                $value = new Node($value);
                $nodes[] = $value;
            }
        }
        
        return $nodes;
    }
    
    /**
     * Check if object has at leas one process property
     * 
     * @param \stdClass $value
     * @return boolean
     */
    protected function hasProcessorProperty($value)
    {
        $processorProps = array_map(function ($processor) {
            return $processor->getProperty();
        }, $this->processors);
        
        $valueProps = array_keys(get_object_vars($value));
        
        return count(array_intersect($valueProps, $processorProps)) > 0;
    }
    
    /**
     * Replace nodes with their results
     * 
     * @param array|object $target
     */
    public function applyNodeResults(&$target)
    {
        foreach ($target as &$value) {
            if ($value instanceof Node) {
                $value = $value->getResult();
            } elseif (is_array($value) || is_object($value)) {
                $this->applyNodeResults($value);
            }
        }
    }
    
    /**
     * Enrich object
     * 
     * @param object $subject
     */
    public static function process($subject)
    {
        $enrich = new static($subject);
        $enrich->applyTo($subject);
    }
}
