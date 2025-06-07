<?php
// src/Web/Controllers/ChatController.php

require_once __DIR__ . '/../../Core/Helpers.php';
require_once __DIR__ . '/../Models/Thread.php';
require_once __DIR__ . '/../Models/Agent.php';

class ChatController {
    
    public function index() {
        // Check if user is logged in
        Helpers::requireWebAuth();
        
        // Get user's threads
        $threads = Thread::getUserThreads(Helpers::getCurrentUserId());
        
        // Get current thread (first one or create new)
        $currentThread = null;
        if (!empty($threads)) {
            $currentThread = $threads[0];
        } else {
            // Create first thread for new user
            $currentThread = Thread::create(Helpers::getCurrentUserId(), 'Welcome Chat');
            $threads = [$currentThread];
        }
        
        // Get messages for current thread
        $messages = Thread::getMessages($currentThread['id']);
        
        // Get available agents for user
        $availableAgents = Agent::getUserAgents(Helpers::getCurrentUserId());
        
        // Check if a specific agent was requested via URL parameter
        $selectedAgentId = null;
        if (isset($_GET['agent']) && is_numeric($_GET['agent'])) {
            $requestedAgent = Agent::findById($_GET['agent']);
            if ($requestedAgent && $requestedAgent->getUserId() == Helpers::getCurrentUserId()) {
                $selectedAgentId = $_GET['agent'];
            }
        }
        
        // Load chat view
        Helpers::loadView('chat', [
            'pageTitle' => 'Chat - OpenAI Webchat',
            'threads' => $threads,
            'currentThread' => $currentThread,
            'messages' => $messages,
            'availableAgents' => $availableAgents,
            'selectedAgentId' => $selectedAgentId
        ]);
    }
}