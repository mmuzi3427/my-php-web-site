<?php
$db_host = getenv('myhost');
$db_user = 'kino63773537bot';
$db_pass = getenv('BAZA_Paroli');
$db_name = 'kino63773537bot_kino';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Baza xatosi: " . $e->getMessage());
}
?>
