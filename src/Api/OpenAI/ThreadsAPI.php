<?php
// src/Api/OpenAI/ThreadsAPI.php - UPDATED PATHS

require_once __DIR__ . '/../../Core/Helpers.php';
require_once __DIR__ . '/../Models/Thread.php';
require_once __DIR__ . '/../Models/Agent.php';
require_once __DIR__ . '/SystemAPI.php';

class ThreadsAPI {
    
    public function getThreads() {
        Helpers::requireAuth();
        
        try {
            $threads = Thread::getUserThreads(Helpers::getCurrentUserId());
            Helpers::jsonResponse($threads);
        } catch (Exception $e) {
            error_log("Error fetching threads: " . $e->getMessage());
            Helpers::jsonError('Failed to fetch threads', 500);
        }
    }
    
    public function createThread() {
        Helpers::requireAuth();
        
        $input = Helpers::getJsonInput();
        Helpers::validateRequired($input, ['title']);
        
        try {
            $thread = Thread::create(Helpers::getCurrentUserId(), $input['title']);
            Helpers::jsonResponse($thread, 201);
        } catch (Exception $e) {
            error_log("Error creating thread: " . $e->getMessage());
            Helpers::jsonError('Failed to create thread', 500);
        }
    }
    
    public function getThread($threadId) {
        Helpers::requireAuth();
        
        try {
            $thread = Thread::findById($threadId);
            
            if (!$thread) {
                Helpers::jsonError('Thread not found', 404);
            }
            
            // Check ownership
            if (!Thread::belongsToUser($threadId, Helpers::getCurrentUserId())) {
                Helpers::jsonError('Access denied', 403);
            }
            
            // Include messages
            $thread['messages'] = Thread::getMessages($threadId);
            
            Helpers::jsonResponse($thread);
        } catch (Exception $e) {
            error_log("Error fetching thread: " . $e->getMessage());
            Helpers::jsonError('Failed to fetch thread', 500);
        }
    }
    
    public function updateThread($threadId) {
        Helpers::requireAuth();
        
        $input = Helpers::getJsonInput();
        Helpers::validateRequired($input, ['title']);
        
        try {
            // Check ownership
            if (!Thread::belongsToUser($threadId, Helpers::getCurrentUserId())) {
                Helpers::jsonError('Access denied', 403);
            }
            
            $thread = Thread::updateTitle($threadId, $input['title']);
            Helpers::jsonResponse($thread);
        } catch (Exception $e) {
            error_log("Error updating thread: " . $e->getMessage());
            Helpers::jsonError('Failed to update thread', 500);
        }
    }
    
    public function deleteThread($threadId) {
        Helpers::requireAuth();
        
        try {
            // Check ownership
            if (!Thread::belongsToUser($threadId, Helpers::getCurrentUserId())) {
                Helpers::jsonError('Access denied', 403);
            }
            
            Thread::delete($threadId);
            Helpers::jsonResponse(['message' => 'Thread deleted successfully'], 204);
        } catch (Exception $e) {
            error_log("Error deleting thread: " . $e->getMessage());
            Helpers::jsonError('Failed to delete thread', 500);
        }
    }
    
    public function getMessages($threadId) {
        Helpers::requireAuth();
        
        try {
            // Check ownership
            if (!Thread::belongsToUser($threadId, Helpers::getCurrentUserId())) {
                Helpers::jsonError('Access denied', 403);
            }
            
            $messages = Thread::getMessages($threadId);
            Helpers::jsonResponse($messages);
        } catch (Exception $e) {
            error_log("Error fetching messages: " . $e->getMessage());
            Helpers::jsonError('Failed to fetch messages', 500);
        }
    }
    
    public function sendMessage($threadId) {
        Helpers::requireAuth();
        
        $input = Helpers::getJsonInput();
        Helpers::validateRequired($input, ['message']);
        
        try {
            // Check ownership
            if (!Thread::belongsToUser($threadId, Helpers::getCurrentUserId())) {
                Helpers::jsonError('Access denied', 403);
            }
            
            $userMessage = trim($input['message']);
            
            // Save user message to thread
            Thread::addMessage($threadId, 'user', $userMessage);
            
            // Get conversation history for context
            $messages = Thread::getMessages($threadId);
            
            // Prepare conversation history for OpenAI (exclude system messages)
            $conversationHistory = [];
            foreach ($messages as $msg) {
                if ($msg['role'] !== 'system') {
                    $conversationHistory[] = [
                        'role' => $msg['role'],
                        'content' => $msg['content']
                    ];
                }
            }
            
            // Use SystemAPI for OpenAI communication
            $systemAPI = new SystemAPI();
            $aiResponse = $systemAPI->simpleChat($userMessage, $conversationHistory);
            
            // Save AI response to thread
            Thread::addMessage($threadId, 'assistant', $aiResponse);
            
            Helpers::jsonResponse([
                'success' => true,
                'response' => $aiResponse,
                'threadId' => $threadId
            ]);
            
        } catch (Exception $e) {
            error_log("Error sending message: " . $e->getMessage());
            Helpers::jsonError('Failed to send message: ' . $e->getMessage(), 500);
        }
    }
}