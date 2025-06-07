<?php
// src/Tools/Math.php

abstract class Tool {
    abstract public function getName(): string;
    abstract public function getDescription(): string;
    abstract public function getParametersSchema(): array;
    abstract public function execute(array $parameters): array;
    
    public function getOpenAIDefinition(): array {
        $schema = $this->getParametersSchema();
        $required = [];
        
        // Extract required parameters correctly
        foreach ($schema as $paramName => $paramConfig) {
            if (isset($paramConfig['required']) && $paramConfig['required'] === true) {
                $required[] = $paramName;
            }
        }
        
        // Clean the schema - remove the 'required' field from individual parameters
        $cleanSchema = [];
        foreach ($schema as $paramName => $paramConfig) {
            $cleanSchema[$paramName] = [
                'type' => $paramConfig['type'],
                'description' => $paramConfig['description']
            ];
        }
        
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'parameters' => [
                    'type' => 'object',
                    'properties' => $cleanSchema,
                    'required' => $required
                ]
            ]
        ];
    }
    
    public function safeExecute(array $parameters): array {
        try {
            $this->validateParameters($parameters);
            return $this->execute($parameters);
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tool' => $this->getName()
            ];
        }
    }
    
    protected function validateParameters(array $parameters): bool {
        $schema = $this->getParametersSchema();
        
        // Check required parameters
        foreach ($schema as $param => $config) {
            if (isset($config['required']) && $config['required'] && !isset($parameters[$param])) {
                throw new InvalidArgumentException("Missing required parameter: {$param}");
            }
        }
        
        return true;
    }
}

class Math extends Tool {
    
    public function getName(): string {
        return 'math';
    }
    
    public function getDescription(): string {
        return 'Perform mathematical calculations safely. Supports basic arithmetic operations and common mathematical functions.';
    }
    
    public function getParametersSchema(): array {
        return [
            'expression' => [
                'type' => 'string',
                'description' => 'Mathematical expression to evaluate (e.g., "2 + 2", "sqrt(16)", "sin(pi/2)")',
                'required' => true
            ]
        ];
    }
    
    public function execute(array $parameters): array {
        $expression = $parameters['expression'];
        
        try {
            // Clean and validate the expression
            $cleanExpression = $this->sanitizeExpression($expression);
            
            // Use eval safely for mathematical expressions only
            $result = $this->safeEval($cleanExpression);
            
            return [
                'success' => true,
                'result' => $result,
                'expression' => $expression,
                'tool' => $this->getName()
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Invalid mathematical expression: ' . $e->getMessage(),
                'expression' => $expression,
                'tool' => $this->getName()
            ];
        }
    }
    
    private function sanitizeExpression($expression) {
        // Remove any potentially dangerous content
        $expression = trim($expression);
        
        // Only allow mathematical characters and functions
        $allowedPattern = '/^[0-9+\-*\/\(\)\.\s,piePIE\^sqrtsincostandlogabsfloorceildegradminmax]+$/';
        
        if (!preg_match($allowedPattern, str_replace(['sqrt', 'sin', 'cos', 'tan', 'log', 'abs', 'floor', 'ceil', 'deg', 'rad', 'min', 'max', 'pi', 'e', 'PI', 'E'], '', $expression))) {
            throw new InvalidArgumentException('Expression contains invalid characters');
        }
        
        // Replace common mathematical constants and functions
        $replacements = [
            'pi' => 'M_PI',
            'PI' => 'M_PI',
            'e' => 'M_E',
            'E' => 'M_E',
            'sqrt(' => 'sqrt(',
            'sin(' => 'sin(',
            'cos(' => 'cos(',
            'tan(' => 'tan(',
            'log(' => 'log(',
            'abs(' => 'abs(',
            'floor(' => 'floor(',
            'ceil(' => 'ceil(',
            'min(' => 'min(',
            'max(' => 'max(',
            '^' => '**' // PHP exponentiation operator
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $expression);
    }
    
    private function safeEval($expression) {
        // Additional safety check
        if (strpos($expression, '$') !== false || 
            strpos($expression, ';') !== false || 
            strpos($expression, 'exec') !== false ||
            strpos($expression, 'system') !== false ||
            strpos($expression, 'shell') !== false) {
            throw new InvalidArgumentException('Potentially dangerous expression detected');
        }
        
        // Evaluate the mathematical expression
        $result = null;
        eval('$result = ' . $expression . ';');
        
        if ($result === null) {
            throw new Exception('Expression could not be evaluated');
        }
        
        return $result;
    }
}