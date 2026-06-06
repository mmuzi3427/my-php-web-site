<?php
$servername = "dpg-d8hpu8uq1p3s73dv0g8g-a";
$username = "mydb_j6ea_user";
$password = "pwen7B20OZAzaSuplkCdeBSOzGKjiij0";
$dbname = "mydb_j6ea";

try {
  $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  // set the PDO error mode to exception
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>
