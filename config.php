<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // <--- CHANGE THIS LINE: Use an empty string for the password
$db = 'shopping_cart';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') '
        . $mysqli->connect_error);
} else {
    echo "Connected successfully to the database.";
}
?>