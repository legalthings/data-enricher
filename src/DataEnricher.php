<?php

namespace LegalThings;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use Jasny\DotKey;

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
        '<ifset>' => Processor\IfSet::class,
        '<ref>' => Processor\Reference::class,
        '<switch>' => Processor\SwitchChoose::class,
        '<merge>' => Processor\Merge::class,
        '<tpl>' => Processor\Mustache::class,
        '<src>' => Processor\Http::class,
        '<jmespath>' => Processor\JmesPath::class,
        '<apply>' => Processor\JmesPath::class,
        '<transformation>' => Processor\Transform::class,
        '<math>' => Processor\Math::class,
        '<enrich>' => Processor\Enrich::class,
        '<dateformat>' => Processor\DateFormat::class,
        '<equal>' => Processor\Equal::class,
        '<match>' => Processor\Match::class,
        '<if>' => Processor\IfElse::class,
        '<join>' => Processor\Join::class,
        
        // Deprecated
        '_ref' => Processor\Reference::class,
        '_switch' => Processor\SwitchChoose::class,
        '_src' => Processor\Http::class,
        '_merge' => Processor\Merge::class,
        '_jmespath' => Processor\JmesPath::class,
        '_tpl' => Processor\Mustache::class,
        '_transformation' => Processor\Transform::class
    ];
    
    
    /**
     * Processors, applied in specified order.
     * 
     * @var DataEnricher\Processor[]
     */
    public $processors;
    
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        foreach (static::$defaultProcessors as $property => $processor) {
            if (is_string($processor)) {
                $processor = new $processor($property);
            }
            
            $this->processors[] = $processor;
        }
    }
    
    /**
     * Create processors
     * 
     * @param object       $source  Data source
     * @param array|object $target  Target or dot key path
     * @return Processor[]
     */
    protected function getProcessorsFor($source, $target)
    {
        $processors = [];
        
        foreach ($this->processors as $processor) {
            $processors[] = $processor->withSourceAndTarget($source, $target);
        }
        
        return $processors;
    }
    
    
    /**
     * Apply processing instructions
     * 
     * @param array|object|string $target  Target or dot key path
     * @param object              $source  Data source
     */
    public function applyTo($target, $source = null)
    {
        if (!isset($source)) {
            $source = $target;
        }
        
        if (!is_object($source)) {
            throw new \Exception("Data enricher on works on an object, not on a " . gettype($source));
        }
        
        if (is_string($target)) {
            $target = DotKey::on($source)->get($target);
        }
        
        $nodes = $this->findNodes($target);
        $processors = $this->getProcessorsFor($source, $target);
        
        foreach ($nodes as $node) {
            foreach ($processors as $processor) {
                $node->apply($processor);
            }
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
     * @param \stdClass    $value
     * @param Processor[]  $processors
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
    protected function applyNodeResults(&$target)
    {
        foreach ($target as &$value) {
            if ($value instanceof Node) {
                $value = $value->getResult();
            } elseif (is_array($value) || is_object($value)) {
                $this->applyNodeResults($value);
            }
        }
    }
}
