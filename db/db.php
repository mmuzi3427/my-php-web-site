<?php
$host = getenv('DB_HOST');
$port = 23488;
$db   = 'defaultdb';
$user = 'avnadmin';
$pass = getenv('DB_PASSWORD'); // "CLICK_TO_REVEAL_PASSWORD" ustiga bosib olasiz
$ssl_cert = __DIR__ . '/ca.pem'; 
// Aiven xavfsizlik uchun SSL talab qiladi (SSL mode: REQUIRED)
$options = [
    PDO::MYSQL_ATTR_SSL_CA => $ssl_cert, 
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "Ulanish muvaffaqiyatli bajarildi! Baza toʻgʻri ishlayapti.";
} catch (\PDOException $e) {
    echo "Ulanishda xatolik: " . $e->getMessage();
}
