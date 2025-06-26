<?php
$mysqli_connection = new MySQLi('mysql.hostinger.it', 'u586070537_fsmf', 'prova123', 'u586070537_data');
if ($mysqli_connection->connect_error) {
   echo "Not connected, error: " . $mysqli_connection->connect_error;
}
else {
   echo "Connected.";
}
?>