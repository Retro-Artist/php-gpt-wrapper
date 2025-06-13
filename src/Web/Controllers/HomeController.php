<?php
// src/Web/Controllers/HomeController.php - UPDATED PATHS

require_once __DIR__ . '/../../Core/Helpers.php';

class HomeController {
    
    public function index() {
        // If user is logged in, redirect to chat
        if (Helpers::isAuthenticated()) {
            Helpers::redirect('/chat');
        }
        
        // Load home/landing page
        Helpers::loadView('home', [
            'pageTitle' => 'OpenAI Webchat - AI-Powered Conversations'
        ]);
    }
}