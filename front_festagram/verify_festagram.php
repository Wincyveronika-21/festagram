<?php
// Quick verification of festagram database
echo "<h2>Festagram Database Verification</h2>";

$mysqli = @mysqli_connect("localhost", "myuser", "mypassword", "festagram");

if(!$mysqli) {
    echo "<p style='color:red;'>Connection failed: " . mysqli_connect_error() . "</p>";
    exit;
}

echo "<p style='color:green;'>✅ Connected to festagram database</p>";

// Check what tables exist
$tables_result = mysqli_query($mysqli, "SHOW TABLES");
echo "<h3>Tables in festagram database:</h3><ul>";
$table_count = 0;
while($row = mysqli_fetch_array($tables_result)) {
    echo "<li>" . $row[0] . "</li>";
    $table_count++;
}
echo "</ul>";
echo "<p>Total tables: $table_count</p>";

// Check participants table specifically
if(mysqli_num_rows(mysqli_query($mysqli, "SHOW TABLES LIKE 'participants'")) > 0) {
    $count_result = mysqli_query($mysqli, "SELECT COUNT(*) as count FROM participants");
    $count = mysqli_fetch_assoc($count_result)['count'];
    echo "<p style='color:green;'>✅ Participants table exists with $count records</p>";
    
    // Test a simple query
    $test_result = mysqli_query($mysqli, "SELECT p_id, fname, emailid FROM participants LIMIT 3");
    if($test_result) {
        echo "<h4>Sample participant data:</h4><ul>";
        while($row = mysqli_fetch_assoc($test_result)) {
            echo "<li>ID: {$row['p_id']}, Name: {$row['fname']}, Email: {$row['emailid']}</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color:red;'>❌ Participants table not found!</p>";
}

mysqli_close($mysqli);
?>