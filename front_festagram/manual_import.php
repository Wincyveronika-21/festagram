<?php
/*
 * Manual Data Import Tool
 * This will help you manually import all tables from zephyr to festagram
 */

echo "<html><head><title>Manual Data Import Tool</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .table-section { margin: 20px 0; padding: 15px; border: 2px solid #007bff; border-radius: 5px; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px; }
    button { background: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
    button:hover { background: #0056b3; }
</style></head><body>";

echo "<h1>🔧 Manual Data Import Tool</h1>";

// Database connections
$zephyr_conn = @mysqli_connect("localhost", "myuser", "mypassword", "zephyr");
$festagram_conn = @mysqli_connect("localhost", "myuser", "mypassword", "festagram");

if(!$zephyr_conn || !$festagram_conn) {
    echo "<div class='error'>❌ Database connection failed</div>";
    exit;
}

// Get all tables from zephyr database
$tables_result = mysqli_query($zephyr_conn, "SHOW TABLES");
$all_tables = [];
while($row = mysqli_fetch_array($tables_result)) {
    $all_tables[] = $row[0];
}

echo "<div class='info'>📋 Found " . count($all_tables) . " tables in zephyr database</div>";

// Check which action to perform
$action = $_GET['action'] ?? 'list';
$table = $_GET['table'] ?? '';

if($action === 'list') {
    // Display all tables with import options
    echo "<h2>📊 Tables Available for Import</h2>";
    
    foreach($all_tables as $table_name) {
        echo "<div class='table-section'>";
        echo "<h3>Table: $table_name</h3>";
        
        // Get row count from zephyr
        $count_result = mysqli_query($zephyr_conn, "SELECT COUNT(*) as count FROM `$table_name`");
        $count = mysqli_fetch_assoc($count_result)['count'];
        
        // Check if table exists in festagram
        $exists_result = mysqli_query($festagram_conn, "SHOW TABLES LIKE '$table_name'");
        $exists = mysqli_num_rows($exists_result) > 0;
        
        if($exists) {
            $fest_count_result = mysqli_query($festagram_conn, "SELECT COUNT(*) as count FROM `$table_name`");
            $fest_count = mysqli_fetch_assoc($fest_count_result)['count'];
            echo "<div class='warning'>⚠️ Table exists in festagram with $fest_count records</div>";
        } else {
            echo "<div class='info'>ℹ️ Table does not exist in festagram</div>";
        }
        
        echo "<p>Records in zephyr: <strong>$count</strong></p>";
        
        echo "<button onclick=\"window.location.href='?action=create&table=$table_name'\">Create Structure</button>";
        echo "<button onclick=\"window.location.href='?action=import&table=$table_name'\">Import Data</button>";
        echo "<button onclick=\"window.location.href='?action=both&table=$table_name'\">Create + Import</button>";
        
        echo "</div>";
    }
    
} elseif($action === 'create' && $table) {
    // Create table structure
    echo "<h2>🏗️ Creating Structure for Table: $table</h2>";
    
    $create_result = mysqli_query($zephyr_conn, "SHOW CREATE TABLE `$table`");
    if($create_result) {
        $create_row = mysqli_fetch_assoc($create_result);
        $create_sql = $create_row['Create Table'];
        
        echo "<div class='info'>📝 SQL Statement:</div>";
        echo "<pre>$create_sql</pre>";
        
        // Drop table if exists (to recreate)
        mysqli_query($festagram_conn, "DROP TABLE IF EXISTS `$table`");
        
        if(mysqli_query($festagram_conn, $create_sql)) {
            echo "<div class='success'>✅ Table structure created successfully</div>";
        } else {
            echo "<div class='error'>❌ Failed to create table: " . mysqli_error($festagram_conn) . "</div>";
        }
    }
    
    echo "<button onclick=\"window.location.href='?action=list'\">← Back to List</button>";
    echo "<button onclick=\"window.location.href='?action=import&table=$table'\">Import Data →</button>";
    
} elseif($action === 'import' && $table) {
    // Import data only
    echo "<h2>📥 Importing Data for Table: $table</h2>";
    
    // Check if table exists
    $exists_result = mysqli_query($festagram_conn, "SHOW TABLES LIKE '$table'");
    if(mysqli_num_rows($exists_result) == 0) {
        echo "<div class='error'>❌ Table '$table' does not exist in festagram. Create structure first.</div>";
        echo "<button onclick=\"window.location.href='?action=create&table=$table'\">Create Structure First</button>";
    } else {
        // Clear existing data
        mysqli_query($festagram_conn, "DELETE FROM `$table`");
        
        // Import data
        $data_result = mysqli_query($zephyr_conn, "SELECT * FROM `$table`");
        $imported = 0;
        
        while($row = mysqli_fetch_assoc($data_result)) {
            $columns = array_keys($row);
            $values = array_values($row);
            
            // Escape values
            for($i = 0; $i < count($values); $i++) {
                if($values[$i] === null) {
                    $values[$i] = 'NULL';
                } else {
                    $values[$i] = "'" . mysqli_real_escape_string($festagram_conn, $values[$i]) . "'";
                }
            }
            
            $insert_sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
            
            if(mysqli_query($festagram_conn, $insert_sql)) {
                $imported++;
            } else {
                echo "<div class='error'>❌ Failed to insert record: " . mysqli_error($festagram_conn) . "</div>";
                break;
            }
        }
        
        echo "<div class='success'>✅ Imported $imported records successfully</div>";
    }
    
    echo "<button onclick=\"window.location.href='?action=list'\">← Back to List</button>";
    
} elseif($action === 'both' && $table) {
    // Create structure and import data
    echo "<h2>🚀 Complete Import for Table: $table</h2>";
    
    // Create structure
    echo "<h3>Step 1: Creating Structure</h3>";
    $create_result = mysqli_query($zephyr_conn, "SHOW CREATE TABLE `$table`");
    if($create_result) {
        $create_row = mysqli_fetch_assoc($create_result);
        $create_sql = $create_row['Create Table'];
        
        // Drop table if exists
        mysqli_query($festagram_conn, "DROP TABLE IF EXISTS `$table`");
        
        if(mysqli_query($festagram_conn, $create_sql)) {
            echo "<div class='success'>✅ Table structure created</div>";
            
            // Import data
            echo "<h3>Step 2: Importing Data</h3>";
            $data_result = mysqli_query($zephyr_conn, "SELECT * FROM `$table`");
            $imported = 0;
            
            while($row = mysqli_fetch_assoc($data_result)) {
                $columns = array_keys($row);
                $values = array_values($row);
                
                // Escape values
                for($i = 0; $i < count($values); $i++) {
                    if($values[$i] === null) {
                        $values[$i] = 'NULL';
                    } else {
                        $values[$i] = "'" . mysqli_real_escape_string($festagram_conn, $values[$i]) . "'";
                    }
                }
                
                $insert_sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
                
                if(mysqli_query($festagram_conn, $insert_sql)) {
                    $imported++;
                } else {
                    echo "<div class='error'>❌ Failed to insert record: " . mysqli_error($festagram_conn) . "</div>";
                    break;
                }
            }
            
            echo "<div class='success'>✅ Complete! Structure created and $imported records imported</div>";
            
        } else {
            echo "<div class='error'>❌ Failed to create table structure</div>";
        }
    }
    
    echo "<button onclick=\"window.location.href='?action=list'\">← Back to List</button>";
}

// Close connections
mysqli_close($zephyr_conn);
mysqli_close($festagram_conn);

echo "</body></html>";
?>