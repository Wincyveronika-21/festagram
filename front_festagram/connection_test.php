<?php
// Simple database connection test
echo "<h2>Database Connection Test</h2>";

// Test the original zephyr database first
echo "<h3>Testing original zephyr database:</h3>";
$mysqli_zephyr = @mysqli_connect("localhost", "myuser", "mypassword", "zephyr");
if($mysqli_zephyr) {
    echo "<p style='color:green;'>✓ Connection to 'zephyr' database successful with myuser/mypassword</p>";
    mysqli_close($mysqli_zephyr);
} else {
    echo "<p style='color:red;'>✗ Connection to 'zephyr' failed: " . mysqli_connect_error() . "</p>";
}

// Test festagram database
echo "<h3>Testing festagram database:</h3>";
$mysqli_festagram = @mysqli_connect("localhost", "myuser", "mypassword", "festagram");
if($mysqli_festagram) {
    echo "<p style='color:green;'>✓ Connection to 'festagram' database successful</p>";
    mysqli_close($mysqli_festagram);
} else {
    echo "<p style='color:red;'>✗ Connection to 'festagram' failed: " . mysqli_connect_error() . "</p>";
}

// Test connection without specifying database to see if we can create one
echo "<h3>Testing connection without database:</h3>";
$mysqli_root = @mysqli_connect("localhost", "myuser", "mypassword");
if($mysqli_root) {
    echo "<p style='color:green;'>✓ Basic connection successful</p>";
    
    // Try to create festagram database
    $create_result = mysqli_query($mysqli_root, "CREATE DATABASE IF NOT EXISTS festagram");
    if($create_result) {
        echo "<p style='color:green;'>✓ Database 'festagram' created or already exists</p>";
    } else {
        echo "<p style='color:red;'>✗ Failed to create database: " . mysqli_error($mysqli_root) . "</p>";
    }
    
    mysqli_close($mysqli_root);
} else {
    echo "<p style='color:red;'>✗ Basic connection failed: " . mysqli_connect_error() . "</p>";
}
?>