<?php
// src/Api/SystemAPI.php

require_once __DIR__ . '/../Core/Helpers.php';

class SystemAPI {
    private $apiKey;
    private $model;
    private $maxTokens;
    private $temperature;
    
    public function __construct() {
        // Load configuration properly
        $this->loadConfig();
        
        if (empty($this->apiKey)) {
            throw new Exception('OpenAI API key not configured');
        }
    }
    
    private function loadConfig() {
        // Try multiple ways to get the config in order of preference
        
        // 1. Try environment variables first (most reliable)
        $this->apiKey = getenv('OPENAI_API_KEY') ?: '';
        $this->model = getenv('OPENAI_MODEL') ?: 'gpt-4o-mini';
        $this->maxTokens = (int)(getenv('OPENAI_MAX_TOKENS') ?: 1024);
        $this->temperature = (float)(getenv('OPENAI_TEMPERATURE') ?: 0.7);
        
        // 2. If environment variables are empty, try config file
        if (empty($this->apiKey)) {
            try {
                if (file_exists(__DIR__ . '/../../config/config.php')) {
                    $config = require __DIR__ . '/../../config/config.php';
                    $this->apiKey = $config['api_key'] ?? '';
                    $this->model = $config['model'] ?? $this->model;
                    $this->maxTokens = $config['max_tokens'] ?? $this->maxTokens;
                    $this->temperature = $config['temperature'] ?? $this->temperature;
                }
            } catch (Exception $e) {
                error_log("Config file loading error: " . $e->getMessage());
            }
        }
        
        // 3. Final validation
        if (empty($this->apiKey)) {
            error_log("OpenAI API key not found in environment variables or config file");
        }
    }
    
    public function getStatus() {
        Helpers::requireAuth();
        
        try {
            $status = [
                'system' => 'OpenAI Webchat',
                'version' => '1.0.0',
                'status' => 'operational',
                'timestamp' => date('c'),
                'database' => $this->checkDatabaseStatus(),
                'openai' => $this->checkOpenAIStatus()
            ];
            
            Helpers::jsonResponse($status);
        } catch (Exception $e) {
            error_log("Error getting system status: " . $e->getMessage());
            Helpers::jsonError('Failed to get system status', 500);
        }
    }
    
    public function getConfig() {
        Helpers::requireAuth();
        
        try {
            $config = [
                'model' => $this->model,
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'features' => [
                    'agents' => true,
                    'tools' => true,
                    'threads' => true
                ]
            ];
            
            Helpers::jsonResponse($config);
        } catch (Exception $e) {
            error_log("Error getting config: " . $e->getMessage());
            Helpers::jsonError('Failed to get configuration', 500);
        }
    }
    
    /**
     * Call OpenAI API for standard chat (used by ThreadsAPI)
     */
    public function callOpenAI($userMessage, $conversationHistory = []) {
        // Prepare messages array for OpenAI
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful AI assistant. Provide clear, helpful, and concise responses.'
            ]
        ];
        
        // Add conversation history (keeping it reasonable length)
        $recentHistory = array_slice($conversationHistory, -10); // Last 10 messages
        $messages = array_merge($messages, $recentHistory);
        
        // Prepare the API request
        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'stream' => false
        ];
        
        // Make the API call
                    $response = $this->makeOpenAICall($payload);
        
        // Extract and return the response
        if (isset($response['choices'][0]['message']['content'])) {
            return trim($response['choices'][0]['message']['content']);
        } else {
            throw new Exception('Invalid response from OpenAI API');
        }
    }
    
    /**
     * Call OpenAI API for agent execution (public method)
     */
    public function callOpenAIAPI($payload) {
        return $this->makeOpenAICall($payload);
    }
    
    private function makeOpenAICall($payload) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Handle curl errors
        if ($response === false) {
            throw new Exception('cURL error: ' . $curlError);
        }
        
        // Parse JSON response
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON parse error: ' . json_last_error_msg());
        }
        
        // Handle HTTP errors
        if ($httpCode !== 200) {
            $errorMessage = isset($decoded['error']['message']) 
                ? $decoded['error']['message'] 
                : "HTTP error: $httpCode";
            throw new Exception("OpenAI API error: " . $errorMessage);
        }
        
        return $decoded;
    }
    
    private function checkDatabaseStatus() {
        try {
            require_once __DIR__ . '/../Core/Database.php';
            $db = Database::getInstance();
            $result = $db->fetch("SELECT 1 as test");
            return $result ? 'connected' : 'disconnected';
        } catch (Exception $e) {
            return 'error';
        }
    }
    
    private function checkOpenAIStatus() {
        try {
            // Simple API test
            $payload = [
                'model' => $this->model,
                'messages' => [['role' => 'user', 'content' => 'Test']],
                'max_tokens' => 5
            ];
            
            $response = $this->makeOpenAICall($payload);
            return isset($response['choices']) ? 'connected' : 'error';
        } catch (Exception $e) {
            return 'error';
        }
    }
}