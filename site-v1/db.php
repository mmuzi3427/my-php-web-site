<?php
// Bular xavfsiz va hammaga ko'rinishi mumkin bo'lgan ma'lumotlar
$db   = 'mydb_j6ea';                  
$user = 'mydb_j6ea_user';             
$port = '5432';                       

// MAXFIY MA'LUMOTLAR - Render'dagi Environment bo'limidan o'qib olinadi:
$host = getenv('DB_HOST');
$pass = getenv('DB_PASSWORD'); 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
} catch (PDOException $e) {
    // Xavfsizlik yuzasidan xatolik ichidagi haqiqiy parollarni ekranga chiqarmaslik uchun:
    die("Baza bilan aloqa o'rnatilmadi.");
}
?>
