<?php
/*
 * Database Setup and Test Script for Festagram Fest Management System
 * 
 * This script helps you:
 * 1. Test database connections with different credentials
 * 2. Create the database if it doesn't exist
 * 3. Import the festagram.sql file
 * 4. Verify the participants table setup
 */

echo "<html><head><title>Festagram Database Setup</title></head><body>";
echo "<h1>Festagram Database Setup and Testing</h1>";

// Common database configurations to try
$configs = [
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'database' => 'festagram'],
    ['host' => 'localhost', 'user' => 'root', 'password' => 'root', 'database' => 'festagram'],
    ['host' => 'localhost', 'user' => 'festagram_user', 'password' => 'festagram_pass', 'database' => 'festagram'],
    ['host' => '127.0.0.1', 'user' => 'root', 'password' => '', 'database' => 'festagram'],
];

echo "<h2>Testing Database Connections</h2>";

$working_config = null;

foreach($configs as $index => $config) {
    echo "<div style='border:1px solid #ccc; margin:10px; padding:10px;'>";
    echo "<h3>Configuration " . ($index + 1) . "</h3>";
    echo "<p><strong>Host:</strong> {$config['host']}</p>";
    echo "<p><strong>User:</strong> {$config['user']}</p>";
    echo "<p><strong>Password:</strong> " . (empty($config['password']) ? '(empty)' : '***') . "</p>";
    echo "<p><strong>Database:</strong> {$config['database']}</p>";
    
    // Test connection
    $mysqli = @mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
    
    if($mysqli) {
        echo "<p style='color:green;'><strong>✓ Connection successful!</strong></p>";
        
        // Test if participants table exists
        $table_check = mysqli_query($mysqli, "SHOW TABLES LIKE 'participants'");
        if(mysqli_num_rows($table_check) > 0) {
            echo "<p style='color:green;'>✓ 'participants' table found</p>";
            
            // Check table structure
            $structure_check = mysqli_query($mysqli, "DESCRIBE participants");
            echo "<p>Table structure:</p><ul>";
            while($row = mysqli_fetch_assoc($structure_check)) {
                echo "<li>{$row['Field']} - {$row['Type']} " . 
                     ($row['Key'] == 'PRI' ? '(PRIMARY KEY)' : '') . 
                     ($row['Extra'] == 'auto_increment' ? '(AUTO INCREMENT)' : '') . "</li>";
            }
            echo "</ul>";
            
            $working_config = $config;
        } else {
            echo "<p style='color:orange;'>⚠ 'participants' table not found</p>";
        }
        
        mysqli_close($mysqli);
    } else {
        echo "<p style='color:red;'><strong>✗ Connection failed</strong></p>";
        echo "<p>Error: " . mysqli_connect_error() . "</p>";
    }
    echo "</div>";
}

if($working_config) {
    echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:15px; margin:20px 0;'>";
    echo "<h2>✓ Working Configuration Found!</h2>";
    echo "<p>Update your <code>linc.php</code> file with these settings:</p>";
    echo "<pre style='background:#f8f9fa; padding:10px; border:1px solid #dee2e6;'>";
    echo '$host="' . $working_config['host'] . '";' . "\n";
    echo '$user="' . $working_config['user'] . '";' . "\n";  
    echo '$pw="' . $working_config['password'] . '";' . "\n";
    echo '$schema="' . $working_config['database'] . '";';
    echo "</pre>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da; border:1px solid #f5c6cb; padding:15px; margin:20px 0;'>";
    echo "<h2>No Working Configuration Found</h2>";
    echo "<p>You need to:</p>";
    echo "<ol>";
    echo "<li>Make sure MySQL/MariaDB is running on your system</li>";
    echo "<li>Create the 'zephyr' database:";
    echo "<pre>CREATE DATABASE zephyr;</pre></li>";
    echo "<li>Import the zephyr.sql file:";
    echo "<pre>mysql -u root -p zephyr < \"path/to/zephyr .sql\"</pre></li>";
    echo "<li>Or use phpMyAdmin to import the SQL file</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h2>Manual Database Creation</h2>";
echo "<p>If you need to create the database manually, you can:</p>";
echo "<ol>";
echo "<li>Open MySQL command line or phpMyAdmin</li>";
echo "<li>Run: <code>CREATE DATABASE zephyr;</code></li>";
echo "<li>Import the SQL file located at: <code>" . dirname(__FILE__) . "/../zephyr .sql</code></li>";
echo "</ol>";

echo "</body></html>";
?>