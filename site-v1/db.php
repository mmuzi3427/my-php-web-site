<?php
$servername = "sql107.infinityfree.com";
$username = "if0_41240747";
$password = "wiNgwPtwYG";
$dbname = "if0_41240747_users";

try {
  $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
  // set the PDO error mode to exception
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>