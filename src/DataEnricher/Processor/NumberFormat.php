<?php

namespace LegalThings\DataEnricher\Processor;

use LegalThings\DataEnricher\Node;
use LegalThings\DataEnricher\Processor;
use NumberFormatter;

/**
 * NumberFormat processor
 */
class NumberFormat implements Processor
{
    use Processor\Implementation;
    
    /**
     * Apply processing to a single node
     * 
     * @param Node $node
     */
    public function applyToNode(Node $node)
    {
        $instruction = $node->getInstruction($this);
        
        if (is_array($instruction) || is_object($instruction)) {
            $instruction = (object)$instruction;
        }
        
        if (!isset($instruction) || !isset($instruction->input)) {
            return;
        }
        
        $locale = isset($instruction->locale) ? $instruction->locale : 'en_US';
        $decimals = isset($instruction->decimals) ? $instruction->decimals : null;
        $currency = isset($instruction->currency) ? $instruction->currency : null;
        $style = $currency ? NumberFormatter::CURRENCY : NumberFormatter::DECIMAL;
        
        $number = new NumberFormatter($locale, $style);
        
        if (isset($decimals)) {
            $number->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        }
        
        $result = $currency ?
            $number->formatCurrency($instruction->input, $currency) :
            $number->format($instruction->input);
        
        $node->setResult($result);
    }
}
