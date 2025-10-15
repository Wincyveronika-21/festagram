<?php
//session_start
@session_start();
$host="localhost";//taking host name of database
$user="myuser";//taking user name of databse
$pw="mypassword";//taking password
$schema="festagram";//taking the schema name to connect
$mysqli=mysqli_connect($host,$user,$pw,$schema);//returns database handle - representation of connectn. of php to database

//CONNECT TO MySQL
if(!$mysqli){
    echo "Connection failed<br>";
    echo "ERROR NUMBER:".mysqli_connect_errno(). "<br>";
    echo "ERROR MESSAGE:".mysqli_connect_error(). "<br>";
    die();
}

//echo "connected successfully<br>";
?>