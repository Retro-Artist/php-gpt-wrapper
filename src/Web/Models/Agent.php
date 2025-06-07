<?php
// src/Web/Models/Agent.php

require_once __DIR__ . '/../../Core/Database.php';
require_once __DIR__ . '/../../Core/Helpers.php';

class Agent {
    private $id;
    private $name;
    private $instructions;
    private $model;
    private $tools = [];
    private $userId;
    private $isActive;
    private $db;
    
    public function __construct($name, $instructions, $model = 'gpt-4o-mini') {
        $this->name = $name;
        $this->instructions = $instructions;
        $this->model = $model;
        $this->userId = Helpers::getCurrentUserId();
        $this->isActive = true;
        $this->db = Database::getInstance();
    }
    
    public function addTool($toolClassName) {
        $this->tools[] = $toolClassName;
        return $this; // For method chaining
    }
    
    public function setInstructions($instructions) {
        $this->instructions = $instructions;
        return $this;
    }
    
    public function setModel($model) {
        $this->model = $model;
        return $this;
    }
    
    public function setActive($isActive) {
        $this->isActive = $isActive;
        return $this;
    }
    
    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function save() {
        if ($this->id) {
            // Update existing agent
            $this->db->update('agents', [
                'name' => $this->name,
                'instructions' => $this->instructions,
                'model' => $this->model,
                'tools' => json_encode($this->tools),
                'is_active' => $this->isActive
            ], 'id = ?', [$this->id]);
        } else {
            // Create new agent
            $this->id = $this->db->insert('agents', [
                'name' => $this->name,
                'instructions' => $this->instructions,
                'model' => $this->model,
                'tools' => json_encode($this->tools),
                'user_id' => $this->userId,
                'is_active' => $this->isActive
            ]);
        }
        
        return $this;
    }
    
    public static function findById($agentId) {
        $db = Database::getInstance();
        $data = $db->fetch("SELECT * FROM agents WHERE id = ?", [$agentId]);
        
        if (!$data) {
            return null;
        }
        
        return self::fromArray($data);
    }
    
    public static function getUserAgents($userId) {
        $db = Database::getInstance();
        $results = $db->fetchAll("
            SELECT * FROM agents 
            WHERE user_id = ? AND is_active = true 
            ORDER BY created_at DESC
        ", [$userId]);
        
        $agents = [];
        foreach ($results as $data) {
            $agents[] = self::fromArray($data);
        }
        
        return $agents;
    }
    
    public static function getAllActiveAgents() {
        $db = Database::getInstance();
        $results = $db->fetchAll("
            SELECT * FROM agents 
            WHERE is_active = true 
            ORDER BY name ASC
        ");
        
        $agents = [];
        foreach ($results as $data) {
            $agents[] = self::fromArray($data);
        }
        
        return $agents;
    }
    
    private static function fromArray($data) {
        $agent = new self($data['name'], $data['instructions'], $data['model']);
        $agent->id = $data['id'];
        $agent->userId = $data['user_id'];
        $agent->isActive = $data['is_active'];
        $agent->tools = json_decode($data['tools'] ?? '[]', true);
        return $agent;
    }
    
    public function execute($message, $threadId) {
        // Create a run for tracking
        $run = $this->createRun($threadId);
        
        try {
            // Execute the agent with tools
            $response = $this->executeWithTools($message, $threadId);
            
            // Complete the run
            $this->completeRun($run['id'], 'completed', $response);
            
            return $response;
            
        } catch (Exception $e) {
            // Mark run as failed
            $this->completeRun($run['id'], 'failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    private function executeWithTools($message, $threadId) {
        // Get conversation history
        require_once __DIR__ . '/Thread.php';
        $messages = Thread::getMessages($threadId);
        
        // Prepare tools for OpenAI
        $tools = $this->prepareTools();
        
        // Prepare messages for OpenAI
        $conversationMessages = [
            [
                'role' => 'system',
                'content' => $this->instructions
            ]
        ];
        
        // Add recent conversation history (last 10 messages)
        $recentMessages = array_slice($messages, -10);
        foreach ($recentMessages as $msg) {
            if ($msg['role'] !== 'system') {
                $conversationMessages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content']
                ];
            }
        }
        
        // Add current user message
        $conversationMessages[] = [
            'role' => 'user',
            'content' => $message
        ];
        
        // Use SystemAPI for OpenAI calls (centralized approach)
        require_once __DIR__ . '/../../Api/SystemAPI.php';
        $systemAPI = new SystemAPI();
        
        // Prepare OpenAI request payload
        $payload = [
            'model' => $this->model,
            'messages' => $conversationMessages,
            'max_tokens' => 1024,
            'temperature' => 0.7
        ];
        
        // Add tools if available
        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }
        
        try {
            // Make initial API call using SystemAPI
            $response = $systemAPI->callOpenAIAPI($payload);
            
            // Validate response structure
            if (!isset($response['choices'][0]['message'])) {
                error_log("Invalid OpenAI response structure: " . json_encode($response));
                throw new Exception('Invalid OpenAI response structure');
            }
            
            $assistantMessage = $response['choices'][0]['message'];
            
            if (isset($assistantMessage['tool_calls']) && !empty($assistantMessage['tool_calls'])) {
                error_log("Agent making " . count($assistantMessage['tool_calls']) . " tool calls");
                
                // Execute tool calls
                $toolResults = $this->executeToolCalls($assistantMessage['tool_calls']);
                
                // Add assistant message with tool calls to conversation
                $conversationMessages[] = $assistantMessage;
                
                // Add tool results to conversation
                foreach ($toolResults as $toolResult) {
                    $conversationMessages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolResult['tool_call_id'],
                        'content' => json_encode($toolResult['result'])
                    ];
                }
                
                // Make another API call with tool results
                $payload['messages'] = $conversationMessages;
                
                try {
                    $finalResponse = $systemAPI->callOpenAIAPI($payload);
                    
                    // Debug log the final response structure
                    error_log("Final OpenAI response: " . json_encode($finalResponse));
                    
                    // More robust validation of final response
                    if (!isset($finalResponse['choices']) || 
                        !is_array($finalResponse['choices']) || 
                        empty($finalResponse['choices']) ||
                        !isset($finalResponse['choices'][0]['message'])) {
                        
                        error_log("Invalid final response structure from OpenAI");
                        
                        // Return a helpful message based on tool results
                        $toolSummary = $this->summarizeToolResults($toolResults);
                        return "I've executed the requested tools and got these results: " . $toolSummary;
                    }
                    
                    $finalMessage = $finalResponse['choices'][0]['message'];
                    $finalContent = $finalMessage['content'] ?? '';
                    
                    // Ensure content is not empty
                    if (empty(trim($finalContent))) {
                        error_log("Final response content is empty");
                        $toolSummary = $this->summarizeToolResults($toolResults);
                        $finalContent = "I've completed your request using the available tools. " . $toolSummary;
                    }
                    
                    return $finalContent;
                    
                } catch (Exception $e) {
                    error_log("Error in final OpenAI call: " . $e->getMessage());
                    
                    // Fallback: provide summary of tool results
                    $toolSummary = $this->summarizeToolResults($toolResults);
                    return "I've executed your request using the available tools, but encountered an issue with the final response. Here's what I found: " . $toolSummary;
                }
                
            } else {
                // No tools needed, return response directly
                $content = $assistantMessage['content'] ?? '';
                
                // Ensure content is not empty
                if (empty(trim($content))) {
                    $content = "I understand your request, but I wasn't able to generate a proper response.";
                }
                
                return $content;
            }
            
        } catch (Exception $e) {
            error_log("Error in agent execution: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create a summary of tool results for fallback responses
     */
    private function summarizeToolResults($toolResults) {
        $summaries = [];
        
        foreach ($toolResults as $result) {
            $toolName = $result['tool_name'] ?? 'Unknown';
            $toolResult = $result['result'] ?? [];
            
            if (isset($toolResult['success']) && $toolResult['success']) {
                switch ($toolName) {
                    case 'weather':
                        if (isset($toolResult['weather']['description'])) {
                            $summaries[] = $toolResult['weather']['description'];
                        }
                        break;
                    case 'math':
                        if (isset($toolResult['result'])) {
                            $summaries[] = "Calculation result: " . $toolResult['result'];
                        }
                        break;
                    case 'search':
                        if (isset($toolResult['results'])) {
                            $summaries[] = "Found " . count($toolResult['results']) . " search results";
                        }
                        break;
                    case 'read_pdf':
                        if (isset($toolResult['word_count'])) {
                            $summaries[] = "Processed PDF with " . $toolResult['word_count'] . " words";
                        }
                        break;
                    default:
                        $summaries[] = "Executed " . $toolName . " successfully";
                }
            } else {
                $summaries[] = "Tool " . $toolName . " encountered an issue";
            }
        }
        
        return implode('. ', $summaries);
    }
    
    private function prepareTools() {
        $tools = [];
        
        foreach ($this->tools as $toolClassName) {
            try {
                // Load the tool class
                $toolFile = __DIR__ . "/../../Tools/{$toolClassName}.php";
                
                if (file_exists($toolFile)) {
                    require_once $toolFile;
                    
                    if (class_exists($toolClassName)) {
                        $tool = new $toolClassName();
                        $tools[] = $tool->getOpenAIDefinition();
                    }
                }
            } catch (Exception $e) {
                error_log("Error loading tool {$toolClassName}: " . $e->getMessage());
            }
        }
        
        return $tools;
    }
    
    private function executeToolCalls($toolCalls) {
        $results = [];
        
        foreach ($toolCalls as $toolCall) {
            try {
                $toolName = $toolCall['function']['name'];
                $parameters = json_decode($toolCall['function']['arguments'], true);
                
                $result = $this->executeTool($toolName, $parameters);
                
                $results[] = [
                    'tool_call_id' => $toolCall['id'],
                    'tool_name' => $toolName,
                    'result' => $result
                ];
                
            } catch (Exception $e) {
                $results[] = [
                    'tool_call_id' => $toolCall['id'],
                    'tool_name' => $toolCall['function']['name'] ?? 'unknown',
                    'result' => [
                        'success' => false,
                        'error' => $e->getMessage()
                    ]
                ];
            }
        }
        
        return $results;
    }
    
    private function executeTool($toolName, $parameters) {
        // Map tool names to class names
        $toolMap = [
            'math' => 'Math',
            'search' => 'Search',
            'weather' => 'Weather',
            'read_pdf' => 'ReadPDF'
        ];
        
        if (!isset($toolMap[$toolName])) {
            throw new Exception("Unknown tool: {$toolName}");
        }
        
        $toolClassName = $toolMap[$toolName];
        $toolFile = __DIR__ . "/../../Tools/{$toolClassName}.php";
        
        if (!file_exists($toolFile)) {
            throw new Exception("Tool file not found: {$toolFile}");
        }
        
        require_once $toolFile;
        
        if (!class_exists($toolClassName)) {
            throw new Exception("Tool class not found: {$toolClassName}");
        }
        
        $tool = new $toolClassName();
        return $tool->safeExecute($parameters);
    }
    
    private function createRun($threadId) {
        $runId = $this->db->insert('runs', [
            'thread_id' => $threadId,
            'agent_id' => $this->id,
            'status' => 'in_progress',
            'started_at' => date('Y-m-d H:i:s')
        ]);
        
        return [
            'id' => $runId,
            'thread_id' => $threadId,
            'agent_id' => $this->id,
            'status' => 'in_progress'
        ];
    }
    
    private function completeRun($runId, $status, $metadata = null) {
        $this->db->update('runs', [
            'status' => $status,
            'completed_at' => date('Y-m-d H:i:s'),
            'metadata' => json_encode($metadata)
        ], 'id = ?', [$runId]);
    }
    
    public function delete() {
        if ($this->id) {
            $this->db->update('agents', ['is_active' => false], 'id = ?', [$this->id]);
        }
        return $this;
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getInstructions() { return $this->instructions; }
    public function getModel() { return $this->model; }
    public function getTools() { return $this->tools; }
    public function getUserId() { return $this->userId; }
    public function isActive() { return $this->isActive; }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'instructions' => $this->instructions,
            'model' => $this->model,
            'tools' => $this->tools,
            'user_id' => $this->userId,
            'is_active' => $this->isActive
        ];
    }
}