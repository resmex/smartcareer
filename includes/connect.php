<?php
$host = 'localhost'; 
$username = 'root';  
$password = 'resmex1807';
$dbname = 'smartcareer'; 

$con = mysqli_connect($host, $username, $password, $dbname);

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
