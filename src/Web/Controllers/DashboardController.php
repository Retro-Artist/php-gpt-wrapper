<?php
// src/Web/Controllers/DashboardController.php  

require_once __DIR__ . '/../../Core/Helpers.php';
require_once __DIR__ . '/../../Api/Models/Thread.php';
require_once __DIR__ . '/../../Api/Models/Agent.php';
require_once __DIR__ . '/../../Api/Models/Run.php';

class DashboardController {
    
    public function index() {
        // Check if user is logged in
        Helpers::requireWebAuth();
        
        $userId = Helpers::getCurrentUserId();
        
        // Get user's agents
        $agents = Agent::getUserAgents($userId);
        
        // Get recent threads
        $recentThreads = Thread::getRecentThreads($userId, 5);
        
        // Get run statistics
        $runStats = Run::getRunStats($userId);
        
        // Get recent runs
        $recentRuns = Run::getUserRuns($userId, 10);
        
        // Calculate agent statistics
        $agentStats = [
            'total' => count($agents),
            'active' => count(array_filter($agents, fn($a) => $a->isActive())),
            'with_tools' => count(array_filter($agents, fn($a) => !empty($a->getTools())))
        ];
        
        // Calculate thread statistics
        $allThreads = Thread::getUserThreads($userId);
        $threadStats = [
            'total' => count($allThreads),
            'with_messages' => count(array_filter($allThreads, fn($t) => $t['message_count'] > 0)),
            'recent' => count(array_filter($allThreads, fn($t) => 
                strtotime($t['created_at']) > strtotime('-7 days')
            ))
        ];
        
        // Load dashboard view
        Helpers::loadView('dashboard', [
            'pageTitle' => 'Dashboard - OpenAI Webchat',
            'agents' => $agents,
            'recentThreads' => $recentThreads,
            'recentRuns' => $recentRuns,
            'agentStats' => $agentStats,
            'threadStats' => $threadStats,
            'runStats' => $runStats ?: [
                'total_runs' => 0,
                'completed_runs' => 0,
                'failed_runs' => 0,
                'running_runs' => 0
            ]
        ]);
    }
}