<?php
/**
 * Database Connection Test Script
 * Upload this file to public/ folder and access via browser
 * URL: https://yourdomain.com/test-db-connection.php
 * 
 * DELETE THIS FILE AFTER TESTING FOR SECURITY!
 */

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
        }
    }
}

$host = getenv('DB_HOST') ?: 'localhost';
$database = getenv('DB_DATABASE') ?: '';
$username = getenv('DB_USERNAME') ?: '';
$password = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: '3306';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        h1 { color: #333; border-bottom: 3px solid #007bff; padding-bottom: 10px; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
        .success { background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0; color: #155724; }
        .error { background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0; color: #721c24; }
        .warning { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; color: #856404; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .btn { display: inline-block; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Connection Test</h1>
        
        <div class="warning">
            <strong>⚠️ Security Warning:</strong> Delete this file after testing! This file exposes database configuration.
        </div>

        <h2>📋 Configuration from .env</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <tr>
                <td><strong>DB_HOST</strong></td>
                <td><code><?php echo htmlspecialchars($host); ?></code></td>
            </tr>
            <tr>
                <td><strong>DB_PORT</strong></td>
                <td><code><?php echo htmlspecialchars($port); ?></code></td>
            </tr>
            <tr>
                <td><strong>DB_DATABASE</strong></td>
                <td><code><?php echo htmlspecialchars($database); ?></code></td>
            </tr>
            <tr>
                <td><strong>DB_USERNAME</strong></td>
                <td><code><?php echo htmlspecialchars($username); ?></code></td>
            </tr>
            <tr>
                <td><strong>DB_PASSWORD</strong></td>
                <td><code><?php echo str_repeat('*', strlen($password)); ?></code> (<?php echo strlen($password); ?> characters)</td>
            </tr>
        </table>

        <h2>🔌 Connection Test</h2>
        <?php
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            
            echo '<div class="success">';
            echo '<strong>✅ Connection Successful!</strong><br>';
            echo 'Successfully connected to database: <code>' . htmlspecialchars($database) . '</code>';
            echo '</div>';
            
            // Get MySQL version
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            echo '<div class="info">';
            echo '<strong>MySQL Version:</strong> ' . htmlspecialchars($version);
            echo '</div>';
            
            // List tables
            echo '<h2>📊 Database Tables</h2>';
            $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                echo '<table>';
                echo '<tr><th>#</th><th>Table Name</th><th>Row Count</th></tr>';
                foreach ($tables as $index => $table) {
                    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                    echo '<tr>';
                    echo '<td>' . ($index + 1) . '</td>';
                    echo '<td><code>' . htmlspecialchars($table) . '</code></td>';
                    echo '<td>' . number_format($count) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<div class="info">No tables found. Database is empty.</div>';
            }
            
            // Check migrations table
            echo '<h2>🔄 Migration Status</h2>';
            $migrationsExist = in_array('migrations', $tables);
            
            if ($migrationsExist) {
                $migrations = $pdo->query('SELECT * FROM migrations ORDER BY batch, id')->fetchAll();
                if (count($migrations) > 0) {
                    echo '<div class="success">Found ' . count($migrations) . ' completed migrations</div>';
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Migration</th><th>Batch</th></tr>';
                    foreach ($migrations as $migration) {
                        echo '<tr>';
                        echo '<td>' . $migration['id'] . '</td>';
                        echo '<td><small>' . htmlspecialchars($migration['migration']) . '</small></td>';
                        echo '<td>' . $migration['batch'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="info">Migrations table exists but is empty. No migrations have been run yet.</div>';
                }
            } else {
                echo '<div class="warning">Migrations table does not exist. You need to run migrations.</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="error">';
            echo '<strong>❌ Connection Failed!</strong><br><br>';
            echo '<strong>Error Code:</strong> ' . $e->getCode() . '<br>';
            echo '<strong>Error Message:</strong> ' . htmlspecialchars($e->getMessage()) . '<br><br>';
            
            echo '<strong>Common Solutions:</strong><br>';
            echo '<ol>';
            echo '<li>Check if database <code>' . htmlspecialchars($database) . '</code> exists in cPanel → MySQL Databases</li>';
            echo '<li>Check if user <code>' . htmlspecialchars($username) . '</code> has privileges to access the database</li>';
            echo '<li>Verify the password is correct (no extra spaces or special characters)</li>';
            echo '<li>Try using <code>127.0.0.1</code> instead of <code>localhost</code> for DB_HOST</li>';
            echo '<li>Check if the database name includes a prefix (e.g., <code>username_dbname</code>)</li>';
            echo '</ol>';
            echo '</div>';
        }
        ?>

        <h2>💡 Next Steps</h2>
        <div class="info">
            <strong>If connection is successful:</strong>
            <ol>
                <li>Go to <code>/admin/migration</code> to run database migrations</li>
                <li><strong>DELETE THIS FILE</strong> for security: <code>public/test-db-connection.php</code></li>
            </ol>
            
            <strong>If connection failed:</strong>
            <ol>
                <li>Fix the database credentials in <code>.env</code> file</li>
                <li>Clear Laravel config cache (delete <code>bootstrap/cache/config.php</code>)</li>
                <li>Refresh this page to test again</li>
            </ol>
        </div>

        <a href="?refresh=1" class="btn">🔄 Test Again</a>
    </div>
</body>
</html>
