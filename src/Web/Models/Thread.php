<?php
// src/Web/Models/Thread.php

require_once __DIR__ . '/../../Core/Database.php';

class Thread {
    private static function getDB() {
        return Database::getInstance();
    }
    
    public static function getUserThreads($userId) {
        $db = self::getDB();
        return $db->fetchAll("
            SELECT t.*, 
                   COUNT(m.id) as message_count,
                   MAX(m.created_at) as last_message_at
            FROM threads t 
            LEFT JOIN messages m ON t.id = m.thread_id 
            WHERE t.user_id = ? 
            GROUP BY t.id 
            ORDER BY t.created_at DESC
        ", [$userId]);
    }
    
    public static function findById($threadId) {
        $db = self::getDB();
        return $db->fetch("SELECT * FROM threads WHERE id = ?", [$threadId]);
    }
    
    public static function create($userId, $title = 'New Conversation') {
        $db = self::getDB();
        $threadId = $db->insert('threads', [
            'user_id' => $userId,
            'title' => $title
        ]);
        
        return self::findById($threadId);
    }
    
    public static function updateTitle($threadId, $title) {
        $db = self::getDB();
        $db->update('threads', 
            ['title' => $title], 
            'id = ?', 
            [$threadId]
        );
        
        return self::findById($threadId);
    }
    
    public static function delete($threadId) {
        $db = self::getDB();
        return $db->delete('threads', 'id = ?', [$threadId]);
    }
    
    public static function getMessages($threadId) {
        $db = self::getDB();
        return $db->fetchAll("
            SELECT * FROM messages 
            WHERE thread_id = ? 
            ORDER BY created_at ASC
        ", [$threadId]);
    }
    
    public static function addMessage($threadId, $role, $content) {
        $db = self::getDB();
        
        // Validate content is not null or empty
        if ($content === null || trim($content) === '') {
            error_log("Warning: Attempted to save message with null/empty content for thread {$threadId}");
            $content = '[Empty response]'; // Fallback content
        }
        
        $messageId = $db->insert('messages', [
            'thread_id' => $threadId,
            'role' => $role,
            'content' => $content
        ]);
        
        // Update thread timestamp
        $db->update('threads', 
            ['updated_at' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$threadId]
        );
        
        return $messageId;
    }
    
    public static function belongsToUser($threadId, $userId) {
        $db = self::getDB();
        $result = $db->fetch("
            SELECT COUNT(*) as count FROM threads 
            WHERE id = ? AND user_id = ?
        ", [$threadId, $userId]);
        
        return $result['count'] > 0;
    }
    
    public static function getRecentThreads($userId, $limit = 10) {
        $db = self::getDB();
        return $db->fetchAll("
            SELECT t.*, 
                   COUNT(m.id) as message_count
            FROM threads t 
            LEFT JOIN messages m ON t.id = m.thread_id 
            WHERE t.user_id = ? 
            GROUP BY t.id 
            ORDER BY t.updated_at DESC 
            LIMIT ?
        ", [$userId, $limit]);
    }
    
    public static function searchThreads($userId, $query) {
        $db = self::getDB();
        $searchTerm = "%{$query}%";
        
        return $db->fetchAll("
            SELECT DISTINCT t.*, 
                   COUNT(m.id) as message_count
            FROM threads t 
            LEFT JOIN messages m ON t.id = m.thread_id 
            WHERE t.user_id = ? 
            AND (t.title LIKE ? OR m.content LIKE ?)
            GROUP BY t.id 
            ORDER BY t.updated_at DESC
        ", [$userId, $searchTerm, $searchTerm]);
    }
}