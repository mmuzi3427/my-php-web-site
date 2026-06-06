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

    // db.php faylingizning eng pastiga qo'shing:

try {
    // Agar blogs jadvali bo'lmasa, uni yaratish
    $query = "CREATE TABLE IF NOT EXISTS blogs (
        id SERIAL PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    
    $pdo->exec($query);
    
    // Tekshirish uchun bazaga vaqtincha 1 ta ma'lumot qo'shib qo'yamiz (agar bo'sh bo'lsa)
    $check = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    if ($check == 0) {
        $pdo->exec("INSERT INTO blogs (title, content) VALUES ('Birinchi maqola', 'Telegram Mini App uchun test kontenti.');");
    }

} catch (PDOException $e) {
    // Agar muammo jadval yaratishda bo'lsa, xatoni ko'rsatish
    echo "Jadval yaratishda xato: " . $e->getMessage();
}
    
?>
