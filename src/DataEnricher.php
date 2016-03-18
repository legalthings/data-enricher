<?php

namespace LegalThings;

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
        '_ref' => 'Reference',
        '_switch' => 'SwitchChoose',
        '_src' => 'Http',
        '_merge' => 'Merge',
        '_jmespath' => 'JmesPath',
        '_tpl' => 'Mustache'
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
                $class = $processor[0] === '\\' ? substr($processor, 1) : __CLASS__ . '\\' . $processor;
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
        if (is_string($target)) $target = \DotKey::on($this->source)->get($target);
        
        foreach ($this->processors as $processor) {
            $processor->applyTo($target);
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
