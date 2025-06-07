# OpenAI Webchat Project Bible

## Project Overview

A clean, maintainable OpenAI-powered webchat application featuring dynamic agent creation, tool-based architecture, and a hybrid web/API system. Built with PHP 8.4.7, MySQL, and Docker.

### Core Philosophy
- **Clarity over complexity** - Every line of code should be easy to understand
- **Maintainability first** - Favor verbose, clear code over clever abstractions
- **Dynamic agent system** - Agents created programmatically, not as static classes
- **Tool-based architecture** - Reusable tools that any agent can use

## Architecture Overview

### Hybrid Web + API Architecture
- **Web Routes**: Public URLs users navigate to (`/chat`, `/login`)
- **API Routes**: Internal AJAX endpoints (`/api/threads`, `/api/messages`)
- **Shared Foundation**: Same models, services, and business logic
- **Different Responses**: Web returns HTML, API returns JSON

## Directory Structure

```
gpt-wrapper/
├── app
│   ├── database.sql
│   ├── generate_agents.php
│   └── migrate.php
├── config
│   ├── config.php          # Configuration 
│   └── load_env.php
├── logs
├── public                  # Web root - Single entry point
│   ├── favicon.ico
│   └── index.php
├── src                                  # Application logic
│   ├── Api                              # API resource controllers (return JSONS)
│   │   ├── AgentsAPI.php
│   │   ├── SystemAPI.php
│   │   ├── ThreadsAPI.php
│   │   └── ToolsAPI.php
│   ├── Core
│   │   ├── Database.php
│   │   ├── Helpers.php
│   │   ├── Logger.php
│   │   ├── Router.php                   # Handles both web and API routes
│   │   └── Security.php
│   ├── Tools                            # Assistant Tool implementations
│   │   ├── Math.php
│   │   ├── ReadPDF.php
│   │   ├── Search.php
│   │   └── Weather.php
│   └── Web
│       ├── Controllers                  # Web page controllers (return HTML)
│       │   ├── AgentController.php
│       │   ├── AuthController.php
│       │   ├── ChatController.php
│       │   ├── DashboardController.php
│       │   └── HomeController.php
│       ├── Models                       # Database entities & abstractions
│       │   ├── User.php
│       │   ├── Agent.php                # Dynamic agent instances
│       │   ├── Run.php                  # Agent execution tracking
│       │   ├── Thread.php               # conversation functionalities
│       │   └── Tool.php                 # Abstract base for all tools
│       └── Views
│           ├── agents.php
│           ├── chat.php
│           ├── dashboard.php
│           ├── error.php
│           ├── home.php
│           ├── layout.php
│           ├── login.php
│           └── register.php
├── tests
├── composer.json
├── docker-compose.yml
├── Dockerfile
├── nginx.conf
└── README.md
```

## Technology Stack

### Core Technologies
- **Backend**: PHP 8.4.7
- **Database**: MySQL 8.0
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose

### Frontend
- **HTML/CSS**: Clean, responsive design
- **JavaScript**: Vanilla JS (no frameworks initially)
- **Styling**: Tailwind CSS (utility-first)
- **AJAX**: Fetch API for API communication

### External Services
- **OpenAI API**: Chat completions and assistants
- **Tools**: Custom implementations for various capabilities

## Naming Conventions

### Core Philosophy
- **OpenAI-Aligned**: Use OpenAI's terminology (`Thread`, `Assistant`, `Run`)
- **Domain-Driven**: Names reflect business domain
- **Future-Proof**: Extensible for advanced features
- **Clear Purpose**: Immediately obvious what each class does

## Code Quality Standards

### Method Naming Patterns
```php
// Service Methods
public function createNewThread($userId, $title = null)
public function getUserThreads($userId)
public function sendMessageToOpenAI($message, $threadId)

// Controller Methods
public function index()           // List resources
public function show($id)         // Show specific resource
public function store()           // Create new resource
public function update($id)       // Update resource
public function destroy($id)      // Delete resource
```

### Error Handling
```php
// Web Controllers
try {
    $result = $this->service->doSomething();
    $this->loadView('success', ['result' => $result]);
} catch (Exception $e) {
    $this->loadView('error', ['message' => $e->getMessage()]);
}

// API Controllers
try {
    $result = $this->service->doSomething();
    $this->jsonResponse($result);
} catch (Exception $e) {
    $this->jsonError($e->getMessage(), 500);
}
```

## Current System Capabilities

### ✅ Complete Agent System
- **Dynamic Agent Creation**: `new Agent("name", "instructions")->save()`
- **Tool Integration**: Agents can use Calculator, WebSearch, CodeInterpreter
- **OpenAI Function Calling**: Automatic tool execution during conversations
- **Agent Management UI**: Create, edit, delete agents via web interface
- **Agent Execution Tracking**: Runs table tracks agent performance

### ✅ Real Usage Examples
```php
// Create a code assistant
$codeBot = new Agent("PHP Expert", "You are a senior PHP developer");
$codeBot->addTool("Calculator")
        ->addTool("CodeInterpreter")
        ->save();

// Create a research assistant  
$researcher = new Agent("Research Analyst", "You analyze data and research topics");
$researcher->addTool("WebSearch")
          ->addTool("Calculator")
          ->save();
```

### ✅ Web Interface Features
- **Agent Dashboard**: View all user agents with stats
- **Agent Creation Modal**: Easy agent creation with tool selection
- **Agent Testing**: Direct link to test agents in chat
- **Agent Management**: Edit, delete, activate/deactivate agents

## Usage Examples

### Creating Agents - Simple & Direct
```php
// Basic chat agent
$chatBot = new Agent("Customer Support", "You help customers with their questions");
$chatBot->save();

// Code assistant with tools and method chaining
$codeBot = new Agent("PHP Developer", "You are a senior PHP developer");
$codeBot->addTool(new CodeInterpreter())
        ->addTool(new DatabaseQuery())
        ->save();

// Research agent with custom model
$researcher = new Agent(
    "Market Analyst", 
    "You are a research specialist",
    "gpt-4-turbo"
);
$researcher->addTool(new WebSearch())
          ->addTool(new FileReader())
          ->save();

// Load and use existing agent
$agent = Agent::findById($agentId);
$response = $agent->execute($userMessage, $threadId);
```

### Frontend JavaScript
```javascript
// Load threads on chat page
async function loadThreads() {
    const response = await fetch('/api/threads');
    const threads = await response.json();
    displayThreads(threads);
}

// Send message
async function sendMessage(threadId, message) {
    const response = await fetch(`/api/threads/${threadId}/messages`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({message: message})
    });
    
    const result = await response.json();
    displayNewMessage(result);
}

// Execute agent
async function runAgent(agentId, message) {
    const response = await fetch(`/api/agents/${agentId}/run`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({message: message, threadId: currentThreadId})
    });
    
    const run = await response.json();
    pollRunStatus(run.id);
}
```

## Security Considerations

### Authentication
- Session-based authentication
- Password hashing with PHP's `password_hash()`
- CSRF protection for forms
- API endpoint authentication

### API Security
- AJAX-only API endpoints
- Authentication required for all API calls
- Input validation and sanitization
- SQL injection prevention with PDO

### Data Protection
- Environment variables for sensitive data
- Secure session configuration
- Input/output sanitization
- Error message sanitization

## Testing Strategy
- **Unit Tests**: Individual models, services, tools
- **Integration Tests**: Controller endpoints
- **Manual Testing**: Full user workflows
- **API Testing**: Postman/Insomnia for API endpoints

## System Status: FULLY OPERATIONAL

Your OpenAI Webchat is now a **complete, production-ready system** with:

### ✅ Core Features Working
- User authentication and session management
- Real-time chat with OpenAI integration
- Thread management and conversation history
- Dynamic agent creation and execution
- Tool system with function calling
- Beautiful, responsive UI

### ✅ Clean Architecture Achieved
- **Router**: Single entry point handling web + API routes
- **Controllers**: Clean separation between Web (HTML) and API (JSON)
- **Models**: Simple, focused database entities
- **Services**: Shared business logic
- **Tools**: Modular, reusable components
- **Views**: Clean HTML templates

### 🚀 Next Steps: Easy Enhancements
1. **Real Web Search**: Integrate Google Custom Search API
2. **File Upload Tool**: Add file processing capabilities  
3. **Agent Templates**: Pre-built agent configurations
4. **Advanced Chat**: Agent selection in chat interface
5. **Export Features**: Download conversations and agent configs

## Quick Start

1. Clone the repository and set up environment:
```bash
git clone https://github.com/Retro-Artist/php83-docker-env.git
cd docker-template
mv .env.example .env
```

2. Start Docker containers and install dependencies:
```bash
docker-compose up -d
docker-compose exec app composer install
```

3. Run database migration:
```bash
docker-compose exec app php database/migrate.php
```

4. Access your development environment:
- **Application**: [http://localhost:8080](http://localhost:8080)
- **phpMyAdmin**: [http://localhost:8081](http://localhost:8081)
  - Server: localhost, Username: root, Password: root_password

## Database Connection

```php
$host = 'localhost';     // Container service name
$dbname = 'simple_php';  // Default database (configurable in .env)
$username = 'root';
$password = 'root_password';
```

## Deployment to Production

1. Clone repository on server
2. Configure Nginx to point to `public` directory
3. Set up MySQL database and update `.env` with production credentials
4. Install dependencies: `composer install --no-dev`
5. Run database migration script

## License

This project is open-sourced software licensed under the MIT license.