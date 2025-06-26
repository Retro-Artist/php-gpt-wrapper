<?php
/**
 * OpenAI Webchat Database Migration Script
 * 
 * This script executes the database.sql file to set up the complete database schema.
 * Run it with: docker-compose exec app php app/migrate.php
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Database connection parameters
$host = getenv('DB_HOST') ?: 'mysql';
$dbname = getenv('DB_DATABASE') ?: 'simple_php';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root_password';

try {
    echo "🚀 Starting OpenAI Webchat Database Migration...\n\n";
    
    // Check if database.sql exists
    $sqlFile = __DIR__ . '/database.sql';
    
    if (!file_exists($sqlFile)) {
        echo "❌ Error: database.sql file not found!\n";
        echo "📁 Expected location: {$sqlFile}\n";
        echo "💡 Please ensure the database.sql file exists in the app/ directory.\n";
        exit(1);
    }
    
    echo "📁 Found database.sql file: " . basename($sqlFile) . "\n";
    echo "📊 File size: " . round(filesize($sqlFile) / 1024, 2) . " KB\n\n";
    
    // Connect to MySQL server first (without specifying database)
    echo "🔗 Connecting to MySQL server...\n";
    $pdo = new PDO("mysql:host={$host}", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "✅ Connected to MySQL server\n";
    
    // Check if database exists, create if it doesn't
    echo "🔍 Checking if database '{$dbname}' exists...\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE '{$dbname}'");
    
    if ($stmt->rowCount() === 0) {
        echo "📝 Database '{$dbname}' not found. Creating it...\n";
        $pdo->exec("CREATE DATABASE `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "✅ Database '{$dbname}' created successfully\n";
    } else {
        echo "✅ Database '{$dbname}' already exists\n";
    }
    
    // Now connect to the specific database
    echo "🔗 Connecting to database '{$dbname}'...\n";
    $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "✅ Connected to database: {$dbname}\n\n";
    
    // Read the SQL file
    echo "📖 Reading database.sql file...\n";
    $sql = file_get_contents($sqlFile);
    
    if (empty($sql)) {
        throw new Exception("database.sql file is empty or could not be read");
    }
    
    echo "✅ SQL file loaded successfully\n\n";
    
    // Begin transaction for safety
    echo "🔄 Starting database migration...\n";
    $pdo->beginTransaction();
    
    try {
        // Disable foreign key checks temporarily for clean slate
        echo "🔧 Preparing database...\n";
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        
        // Execute the SQL file
        echo "⚡ Executing database schema...\n";
        $pdo->exec($sql);
        
        // Re-enable foreign key checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        
        // Commit the transaction
        $pdo->commit();
        
        echo "✅ Database migration completed successfully!\n\n";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $pdo->rollback();
        throw $e;
    }
    
    // Verify the setup
    echo "🔍 Verifying database setup...\n";
    
    $expectedTables = ['users', 'threads', 'agents', 'runs'];
    $totalRecords = 0;
    $tablesCreated = 0;
    
    foreach ($expectedTables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() > 0) {
                $count = $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
                echo "  ✅ Table '{$table}' exists with {$count} records\n";
                $totalRecords += $count;
                $tablesCreated++;
            } else {
                echo "  ❌ Table '{$table}' not found\n";
            }
        } catch (PDOException $e) {
            echo "  ❌ Error checking table '{$table}': " . $e->getMessage() . "\n";
        }
    }
    
    // Verify foreign key constraints
    echo "\n🔗 Verifying foreign key constraints...\n";
    $constraints = $pdo->query("
        SELECT 
            TABLE_NAME,
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE REFERENCED_TABLE_SCHEMA = '{$dbname}' 
        AND REFERENCED_TABLE_NAME IS NOT NULL
        ORDER BY TABLE_NAME, CONSTRAINT_NAME
    ")->fetchAll();
    
    if (empty($constraints)) {
        echo "  ⚠️  No foreign key constraints found\n";
    } else {
        foreach ($constraints as $constraint) {
            echo "  ✅ {$constraint['TABLE_NAME']} -> {$constraint['REFERENCED_TABLE_NAME']} ({$constraint['CONSTRAINT_NAME']})\n";
        }
    }
    
    // Check for demo data
    echo "\n👤 Checking demo data...\n";
    try {
        $demoUser = $pdo->query("SELECT username FROM users WHERE username = 'demo'")->fetchColumn();
        if ($demoUser) {
            echo "  ✅ Demo user 'demo' found\n";
            
            $agentCount = $pdo->query("SELECT COUNT(*) FROM agents WHERE user_id = 1")->fetchColumn();
            echo "  ✅ {$agentCount} demo agents created\n";
            
            $threadCount = $pdo->query("SELECT COUNT(*) FROM threads WHERE user_id = 1")->fetchColumn();
            echo "  ✅ {$threadCount} demo thread(s) created\n";
        } else {
            echo "  ⚠️  Demo user not found\n";
        }
    } catch (PDOException $e) {
        echo "  ❌ Error checking demo data: " . $e->getMessage() . "\n";
    }
    
    // Summary
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "🎉 Migration Summary:\n";
    echo "   📊 Tables created: {$tablesCreated}/" . count($expectedTables) . "\n";
    echo "   📝 Total records: {$totalRecords}\n";
    echo "   🔗 Foreign keys: " . count($constraints) . " constraints\n";
    echo "   ✅ Status: " . ($tablesCreated === count($expectedTables) ? "SUCCESS" : "PARTIAL") . "\n";
    echo str_repeat("=", 60) . "\n\n";
    
    if ($tablesCreated === count($expectedTables)) {
        echo "🎯 Database migration completed successfully!\n\n";
        
        echo "🔑 Demo account ready:\n";
        echo "    Username: demo\n";
        echo "    Password: password\n";
        echo "    Email: demo@example.com\n\n";
        
        echo "🌐 Next steps:\n";
        echo "    1. Access your app: http://localhost:8080\n";
        echo "    2. Login with demo account\n";
        echo "    3. Test the chat functionality\n";
        echo "    4. Try out the demo agents\n\n";
        
        echo "🗄️  Database admin: http://localhost:8081 (phpMyAdmin)\n";
        echo "    Server: localhost\n";
        echo "    Username: root\n";
        echo "    Password: root_password\n\n";
        
        echo "✨ Your OpenAI Webchat is ready to use!\n";
    } else {
        echo "⚠️  Migration completed with warnings. Please check the output above.\n";
        exit(1);
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n\n";
    echo "🔧 Troubleshooting tips:\n";
    echo "    • Check if MySQL container is running: docker-compose ps\n";
    echo "    • Verify database credentials in .env file\n";
    echo "    • Check Docker logs: docker-compose logs mysql\n";
    echo "    • Ensure database '{$dbname}' exists\n\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ General Error: " . $e->getMessage() . "\n\n";
    echo "🔧 Please check:\n";
    echo "    • File permissions on database.sql\n";
    echo "    • SQL syntax in database.sql\n";
    echo "    • Available disk space\n\n";
    exit(1);
}