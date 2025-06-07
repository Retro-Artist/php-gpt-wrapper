<?php
/**
 * Generate Test Agents Script
 * 
 * Creates 3 different specialized agents for testing the system
 * Command: docker-compose exec app php app/generate_agents.php
 */

// Start session for user context
session_start();

// Load configuration and models
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Helpers.php';
require_once __DIR__ . '/../src/Web/Models/Agent.php';

echo "🤖 Creating Test Agents...\n\n";

// Set demo user (you can change this to your actual user ID)
$_SESSION['user_id'] = 1; // Demo user ID

try {
    // 1. 📊 Data Analyst Agent
    echo "Creating Data Analyst Agent...\n";
    $dataAnalyst = new Agent(
        "Data Analyst Pro",
        "You are a professional data analyst and researcher. You excel at analyzing documents, extracting insights from PDFs, performing statistical calculations, and conducting web research to gather supporting data. You provide clear, data-driven insights with actionable recommendations. Always cite your sources and show your mathematical work when performing calculations.",
        "gpt-4o-mini"
    );
    
    $dataAnalyst->addTool("ReadPDF")      // Core: Read and analyze documents
              ->addTool("Math")          // Essential: Statistical calculations  
              ->addTool("Search")        // Supporting: Research additional data
              ->save();
    
    echo "✅ Data Analyst Pro created with ID: " . $dataAnalyst->getId() . "\n";
    echo "   🔧 Tools: ReadPDF, Math, Search\n";
    echo "   📋 Specializes in: Document analysis, statistics, research\n\n";

    // 2. 🌍 Research Assistant Agent  
    echo "Creating Research Assistant Agent...\n";
    $researcher = new Agent(
        "Research Assistant",
        "You are a comprehensive research assistant who helps users gather, analyze, and synthesize information from multiple sources. You excel at reading PDF documents, searching for current information online, and providing weather context when relevant to research topics. You present findings in a clear, well-organized manner with proper citations and cross-references between sources.",
        "gpt-4o"
    );
    
    $researcher->addTool("ReadPDF")       // Core: Analyze research documents
              ->addTool("Search")        // Essential: Web research
              ->addTool("Weather")       // Supporting: Environmental context
              ->save();
    
    echo "✅ Research Assistant created with ID: " . $researcher->getId() . "\n";
    echo "   🔧 Tools: ReadPDF, Search, Weather\n";
    echo "   📋 Specializes in: Information gathering, document review, contextual research\n\n";

    // 3. 🚀 Super Agent (All Tools)
    echo "Creating Super Agent...\n";
    $superAgent = new Agent(
        "Super Agent Ultimate",
        "You are the ultimate AI assistant with access to all available tools. You can read and analyze PDF documents, perform complex mathematical calculations, search the web for current information, and provide weather data. You intelligently choose the right tools for each task and can handle complex multi-step problems that require combining different capabilities. You explain your tool usage and reasoning process to users.",
        "gpt-4o"
    );
    
    $superAgent->addTool("ReadPDF")       // Document analysis
              ->addTool("Math")          // Mathematical calculations
              ->addTool("Search")        // Web research
              ->addTool("Weather")       // Weather information
              ->save();
    
    echo "✅ Super Agent Ultimate created with ID: " . $superAgent->getId() . "\n";
    echo "   🔧 Tools: ALL TOOLS (ReadPDF, Math, Search, Weather)\n";
    echo "   📋 Specializes in: Everything! Multi-tool problem solving\n\n";

    echo "🎉 All test agents created successfully!\n\n";
    
    // Show usage examples
    echo "📝 USAGE EXAMPLES:\n\n";
    
    echo "🔹 Data Analyst Pro - Try these prompts:\n";
    echo "   • \"Analyze this sales report PDF and calculate the growth rate\"\n";
    echo "   • \"Calculate the mean, median, and standard deviation of these numbers: 45, 67, 23, 89, 56, 34, 78\"\n";
    echo "   • \"Research current market trends for renewable energy and analyze the data\"\n\n";
    
    echo "🔹 Research Assistant - Try these prompts:\n";
    echo "   • \"Research the latest developments in AI and summarize key findings from this PDF\"\n";
    echo "   • \"Find current information about climate change impacts and check today's weather patterns\"\n";
    echo "   • \"Review this research paper and find supporting studies online\"\n\n";
    
    echo "🔹 Super Agent Ultimate - Try these prompts:\n";
    echo "   • \"Read this PDF, calculate the financial projections, research market conditions, and factor in weather impacts for outdoor events\"\n";
    echo "   • \"Help me plan a data-driven marketing campaign using document analysis, calculations, and current market research\"\n";
    echo "   • \"Analyze this technical document, verify the mathematical formulas, and research related current developments\"\n\n";
    
    echo "🎯 To test the agents:\n";
    echo "1. Go to http://localhost:8080/chat\n";
    echo "2. Select an agent from the dropdown\n";
    echo "3. Try the example prompts above\n";
    echo "4. Watch how each agent uses different tools for their specialties!\n\n";
    
    echo "💡 Pro tip: The Super Agent will automatically choose the best tools for complex tasks!\n";

} catch (Exception $e) {
    echo "❌ Error creating agents: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n🚀 Agent generation complete! Your system now has 3 specialized AI agents ready for testing.\n";