<?php
/*
 * Database Migration Script: Zephyr to Festagram
 * This script will help migrate your data from zephyr to festagram database
 */

echo "<html><head><title>Database Migration: Zephyr to Festagram</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .step { margin: 20px 0; padding: 15px; border: 2px solid #007bff; border-radius: 5px; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; }
</style></head><body>";

echo "<h1>🚀 Database Migration: Zephyr → Festagram</h1>";

// Database credentials
$host = "localhost";
$user = "myuser";
$password = "mypassword";

echo "<div class='step'>";
echo "<h2>Step 1: Testing Connections</h2>";

// Test connection to zephyr database
echo "<h3>Testing Zephyr Database Connection:</h3>";
$zephyr_conn = @mysqli_connect($host, $user, $password, "zephyr");
if($zephyr_conn) {
    echo "<div class='success'>✅ Successfully connected to 'zephyr' database</div>";
    
    // Count records in participants table
    $count_query = "SELECT COUNT(*) as count FROM participants";
    $count_result = mysqli_query($zephyr_conn, $count_query);
    if($count_result) {
        $row = mysqli_fetch_assoc($count_result);
        echo "<div class='info'>📊 Found {$row['count']} participant records to migrate</div>";
    }
} else {
    echo "<div class='error'>❌ Failed to connect to 'zephyr' database: " . mysqli_connect_error() . "</div>";
    echo "</div></body></html>";
    exit;
}

// Test basic connection (without database)
echo "<h3>Testing Basic MySQL Connection:</h3>";
$basic_conn = @mysqli_connect($host, $user, $password);
if($basic_conn) {
    echo "<div class='success'>✅ Basic MySQL connection successful</div>";
} else {
    echo "<div class='error'>❌ Basic MySQL connection failed: " . mysqli_connect_error() . "</div>";
    echo "</div></body></html>";
    exit;
}

echo "</div>";

echo "<div class='step'>";
echo "<h2>Step 2: Create Festagram Database</h2>";

// Check if festagram database exists
$db_check = mysqli_query($basic_conn, "SHOW DATABASES LIKE 'festagram'");
if(mysqli_num_rows($db_check) > 0) {
    echo "<div class='warning'>⚠️ Database 'festagram' already exists</div>";
} else {
    // Create festagram database
    if(mysqli_query($basic_conn, "CREATE DATABASE festagram")) {
        echo "<div class='success'>✅ Database 'festagram' created successfully</div>";
    } else {
        echo "<div class='error'>❌ Failed to create database 'festagram': " . mysqli_error($basic_conn) . "</div>";
        echo "</div></body></html>";
        exit;
    }
}

echo "</div>";

echo "<div class='step'>";
echo "<h2>Step 3: Copy Database Structure and Data</h2>";

// Connect to festagram database
$festagram_conn = @mysqli_connect($host, $user, $password, "festagram");
if(!$festagram_conn) {
    echo "<div class='error'>❌ Failed to connect to 'festagram' database: " . mysqli_connect_error() . "</div>";
    echo "</div></body></html>";
    exit;
}

// Get list of tables from zephyr database
$tables_query = "SHOW TABLES";
$tables_result = mysqli_query($zephyr_conn, $tables_query);
$tables = [];
while($row = mysqli_fetch_array($tables_result)) {
    $tables[] = $row[0];
}

echo "<div class='info'>📋 Found " . count($tables) . " tables to migrate: " . implode(', ', $tables) . "</div>";

// Copy each table
foreach($tables as $table) {
    echo "<h4>Migrating table: $table</h4>";
    
    // Get CREATE TABLE statement
    $create_query = "SHOW CREATE TABLE `$table`";
    $create_result = mysqli_query($zephyr_conn, $create_query);
    if($create_result) {
        $create_row = mysqli_fetch_assoc($create_result);
        $create_statement = $create_row['Create Table'];
        
        // Execute CREATE TABLE in festagram database
        if(mysqli_query($festagram_conn, $create_statement)) {
            echo "<div class='success'>✅ Table structure created</div>";
            
            // Copy data
            $data_query = "SELECT * FROM `$table`";
            $data_result = mysqli_query($zephyr_conn, $data_query);
            $record_count = 0;
            
            while($data_row = mysqli_fetch_assoc($data_result)) {
                $columns = array_keys($data_row);
                $values = array_values($data_row);
                
                // Escape values
                for($i = 0; $i < count($values); $i++) {
                    $values[$i] = "'" . mysqli_real_escape_string($festagram_conn, $values[$i]) . "'";
                }
                
                $insert_query = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
                
                if(mysqli_query($festagram_conn, $insert_query)) {
                    $record_count++;
                } else {
                    echo "<div class='error'>❌ Failed to insert record: " . mysqli_error($festagram_conn) . "</div>";
                }
            }
            
            echo "<div class='success'>✅ Copied $record_count records</div>";
            
        } else {
            echo "<div class='error'>❌ Failed to create table: " . mysqli_error($festagram_conn) . "</div>";
        }
    }
}

echo "</div>";

echo "<div class='step'>";
echo "<h2>Step 4: Verification</h2>";

// Verify data in festagram database
echo "<h3>Verifying migrated data:</h3>";
foreach($tables as $table) {
    $count_zephyr = mysqli_fetch_assoc(mysqli_query($zephyr_conn, "SELECT COUNT(*) as count FROM `$table`"))['count'];
    $count_festagram = mysqli_fetch_assoc(mysqli_query($festagram_conn, "SELECT COUNT(*) as count FROM `$table`"))['count'];
    
    if($count_zephyr == $count_festagram) {
        echo "<div class='success'>✅ $table: $count_festagram records (matches original)</div>";
    } else {
        echo "<div class='error'>❌ $table: $count_festagram records (original had $count_zephyr)</div>";
    }
}

echo "</div>";

echo "<div class='step'>";
echo "<h2>Step 5: Next Steps</h2>";
echo "<div class='info'>";
echo "<h3>🎉 Migration Complete!</h3>";
echo "<p>Your data has been successfully migrated from 'zephyr' to 'festagram' database.</p>";
echo "<h4>To complete the switch:</h4>";
echo "<ol>";
echo "<li>Update your <code>linc.php</code> file to use 'festagram' database</li>";
echo "<li>Test your application</li>";
echo "<li>Once confirmed working, you can optionally remove the old 'zephyr' database</li>";
echo "</ol>";
echo "</div>";
echo "</div>";

// Close connections
mysqli_close($zephyr_conn);
mysqli_close($festagram_conn);
mysqli_close($basic_conn);

echo "</body></html>";
?>