<?php
// src/Web/Controllers/AgentController.php  

require_once __DIR__ . '/../../Core/Helpers.php';
require_once __DIR__ . '/../../Api/Models/Agent.php';

class AgentController {
    
    public function index() {
        // Check if user is logged in
        Helpers::requireWebAuth();
        
        // Get user's agents
        $agents = Agent::getUserAgents(Helpers::getCurrentUserId());
        
        // Load agent management view
        Helpers::loadView('agents', [
            'pageTitle' => 'Agents - OpenAI Webchat',
            'agents' => $agents
        ]);
    }
}