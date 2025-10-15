<?php
// Simple migration script - just copy participants table
echo "<h2>Simple Migration: Copy Participants Table</h2>";

$zephyr_conn = @mysqli_connect("localhost", "myuser", "mypassword", "zephyr");
$festagram_conn = @mysqli_connect("localhost", "myuser", "mypassword", "festagram");

if(!$zephyr_conn || !$festagram_conn) {
    echo "<p style='color:red;'>Connection failed</p>";
    exit;
}

// Drop existing tables in festagram to start fresh
$tables = ['participants', 'events', 'managers', 'volunteers', 'sponsor', 'venue'];
foreach($tables as $table) {
    mysqli_query($festagram_conn, "DROP TABLE IF EXISTS `$table`");
}

// Copy participants table structure and data
$create_result = mysqli_query($zephyr_conn, "SHOW CREATE TABLE participants");
$create_row = mysqli_fetch_assoc($create_result);
$create_sql = $create_row['Create Table'];

if(mysqli_query($festagram_conn, $create_sql)) {
    echo "<p style='color:green;'>✅ Participants table structure created</p>";
    
    // Copy all participant data
    $data_result = mysqli_query($zephyr_conn, "SELECT * FROM participants");
    $count = 0;
    
    while($row = mysqli_fetch_assoc($data_result)) {
        $fields = implode('`,`', array_keys($row));
        $values = "'" . implode("','", array_map(function($v) use ($festagram_conn) {
            return mysqli_real_escape_string($festagram_conn, $v);
        }, array_values($row))) . "'";
        
        $insert_sql = "INSERT INTO participants (`$fields`) VALUES ($values)";
        if(mysqli_query($festagram_conn, $insert_sql)) {
            $count++;
        }
    }
    
    echo "<p style='color:green;'>✅ Copied $count participant records</p>";
} else {
    echo "<p style='color:red;'>❌ Failed to create participants table</p>";
}

// Copy other essential tables
$essential_tables = ['events', 'venue'];
foreach($essential_tables as $table) {
    echo "<h3>Copying $table table:</h3>";
    
    $create_result = mysqli_query($zephyr_conn, "SHOW CREATE TABLE `$table`");
    if($create_result) {
        $create_row = mysqli_fetch_assoc($create_result);
        $create_sql = $create_row['Create Table'];
        
        if(mysqli_query($festagram_conn, $create_sql)) {
            echo "<p style='color:green;'>✅ Table structure created</p>";
            
            // Copy data
            $data_result = mysqli_query($zephyr_conn, "SELECT * FROM `$table`");
            $count = 0;
            
            while($row = mysqli_fetch_assoc($data_result)) {
                $fields = implode('`,`', array_keys($row));
                $values = "'" . implode("','", array_map(function($v) use ($festagram_conn) {
                    return mysqli_real_escape_string($festagram_conn, $v);
                }, array_values($row))) . "'";
                
                $insert_sql = "INSERT INTO `$table` (`$fields`) VALUES ($values)";
                if(mysqli_query($festagram_conn, $insert_sql)) {
                    $count++;
                }
            }
            echo "<p style='color:green;'>✅ Copied $count records</p>";
        }
    }
}

mysqli_close($zephyr_conn);
mysqli_close($festagram_conn);

echo "<h3>🎉 Migration Complete!</h3>";
echo "<p>Essential tables have been migrated. You can now test your application.</p>";
?>