<?php
session_start();
require "db.php"; // Bu fayl ichida $pdo o'zgaruvchisi yaratiladi

$title = "Blogim";
$blogs = "active";

$_SESSION["admin"] = "Admin";
if(isset($_SESSION["post-yaratildi"])){
    $message = $_SESSION["post-yaratildi"];
}
$qoshildi = isset($_SESSION["post-yaratildi"]);
$edited = isset($_SESSION["post-ozgartirildi"]);

// XATOLIKNING ALDINI OLISH: $pdo mavjudligini tekshiramiz
if (isset($pdo) && $pdo !== null) {
    
    // MUHIM: Jadval nomini bazadagiga moslab 'blogs' (yoki 'posts') qiling
    $statement = $pdo->prepare("SELECT * FROM blogs ORDER BY id DESC"); 
    $statement->execute();
    $posts = $statement->fetchAll();

    // O'chirish (Delete) amali
    if($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["DELETE"])){
        $post_id = $_POST["post_id"];
        $statement = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
        $statement->execute([$post_id]);
        header("Location: blogs.php");
        exit;
    }
    
} else {
    // Agar baza hali ulanmagan bo'lsa, xato bermasligi uchun bo'sh massiv berib turamiz
    $posts = [];
    echo "<p style='color:red;'>Diqqat: Ma'lumotlar bazasiga ulanish o'rnatilmagan!</p>";
}

require "includes/header.php";
?>

<?php 
  require "includes/footer.php";
?>
