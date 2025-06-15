<?php
// src/Api/OpenAI/AgentsAPI.php  

require_once __DIR__ . '/../../Core/Helpers.php';
require_once __DIR__ . '/../Models/Agent.php';
require_once __DIR__ . '/../Models/Thread.php';

class AgentsAPI {
    
    public function getAgents() {
        Helpers::requireAuth();
        
        try {
            $agents = Agent::getUserAgents(Helpers::getCurrentUserId());
            
            // Convert to array format
            $agentData = [];
            foreach ($agents as $agent) {
                $agentData[] = $agent->toArray();
            }
            
            Helpers::jsonResponse($agentData);
        } catch (Exception $e) {
            error_log("Error fetching agents: " . $e->getMessage());
            Helpers::jsonError('Failed to fetch agents', 500);
        }
    }
    
    public function createAgent() {
        Helpers::requireAuth();
        
        $input = Helpers::getJsonInput();
        Helpers::validateRequired($input, ['name', 'instructions']);
        
        try {
            // Create new agent
            $agent = new Agent(
                $input['name'],
                $input['instructions'],
                $input['model'] ?? 'gpt-4o-mini'
            );
            
            // Add tools if provided
            if (isset($input['tools']) && is_array($input['tools'])) {
                foreach ($input['tools'] as $tool) {
                    $agent->addTool($tool);
                }
            }
            
            // Save agent
            $agent->save();
            
            Helpers::jsonResponse($agent->toArray(), 201);
        } catch (Exception $e) {
            error_log("Error creating agent: " . $e->getMessage());
            Helpers::jsonError('Failed to create agent: ' . $e->getMessage(), 500);
        }
    }
    
    public function getAgent($agentId) {
        Helpers::requireAuth();
        
        try {
            $agent = Agent::findById($agentId);
            
            if (!$agent) {
                Helpers::jsonError('Agent not found', 404);
            }
            
            // Check ownership
            if ($agent->getUserId() != Helpers::getCurrentUserId()) {
                Helpers::jsonError('Access denied', 403);
            }
            
            Helpers::jsonResponse($agent->toArray());
        } catch (Exception $e) {
            error_log("Error fetching agent: " . $e->getMessage());
            Helpers::jsonError('Failed to fetch agent', 500);
        }
    }
    
    public function updateAgent($agentId) {
        Helpers::requireAuth();
        
        $input = Helpers::getJsonInput();
        
        try {
            $agent = Agent::findById($agentId);
            
            if (!$agent) {
                Helpers::jsonError('Agent not found', 404);
            }
            
            // Check ownership
            if ($agent->getUserId() != Helpers::getCurrentUserId()) {
                Helpers::jsonError('Access denied', 403);
            }
            
            // Create updated agent
            $updatedAgent = new Agent(
                $input['name'] ?? $agent->getName(),
                $input['instructions'] ?? $agent->getInstructions(),
                $input['model'] ?? $agent->getModel()
            );
            
            // Set the ID to update existing record
            $updatedAgent->setId($agent->getId());
            
            // Add tools
            if (isset($input['tools']) && is_array($input['tools'])) {
                foreach ($input['tools'] as $tool) {
                    $updatedAgent->addTool($tool);
                }
            } else {
                // Keep existing tools
                foreach ($agent->getTools() as $tool) {
                    $updatedAgent->addTool($tool);
                }
            }
            
            $updatedAgent->save();
            
            Helpers::jsonResponse($updatedAgent->toArray());
        } catch (Exception $e) {
            error_log("Error updating agent: " . $e->getMessage());
            Helpers::jsonError('Failed to update agent', 500);
        }
    }
    
    public function deleteAgent($agentId) {
        Helpers::requireAuth();
        
        try {
            $agent = Agent::findById($agentId);
            
            if (!$agent) {
                Helpers::jsonError('Agent not found', 404);
            }
            
            // Check ownership
            if ($agent->getUserId() != Helpers::getCurrentUserId()) {
                Helpers::jsonError('Access denied', 403);
            }
            
            $agent->delete();
            
            Helpers::jsonResponse(['message' => 'Agent deleted successfully'], 204);
        } catch (Exception $e) {
            error_log("Error deleting agent: " . $e->getMessage());
            Helpers::jsonError('Failed to delete agent', 500);
        }
    }
    
    public function runAgent($agentId) {
        Helpers::requireAuth();
        
        $input = Helpers::getJsonInput();
        Helpers::validateRequired($input, ['message', 'threadId']);
        
        try {
            $agent = Agent::findById($agentId);
            
            if (!$agent) {
                Helpers::jsonError('Agent not found', 404);
            }
            
            // Check agent ownership
            if ($agent->getUserId() != Helpers::getCurrentUserId()) {
                Helpers::jsonError('Access denied', 403);
            }
            
            // Verify thread ownership
            if (!Thread::belongsToUser($input['threadId'], Helpers::getCurrentUserId())) {
                Helpers::jsonError('Thread access denied', 403);
            }
            
            // Save user message first
            Thread::addMessage($input['threadId'], 'user', $input['message']);
            
            // Execute agent
            $response = $agent->execute($input['message'], $input['threadId']);
            
            // Validate response before saving
            if ($response === null || trim($response) === '') {
                error_log("Agent execution returned null/empty response for agent {$agentId}");
                $response = "I apologize, but I encountered an issue processing your request. Please try again.";
            }
            
            // Save agent response
            Thread::addMessage($input['threadId'], 'assistant', $response);
            
            Helpers::jsonResponse([
                'success' => true,
                'response' => $response,
                'agentId' => $agentId,
                'threadId' => $input['threadId']
            ]);
        } catch (Exception $e) {
            error_log("Error executing agent: " . $e->getMessage());
            Helpers::jsonError('Failed to execute agent: ' . $e->getMessage(), 500);
        }
    }
}