<?php
require "db.php";
$title = "Blogim";
$blogs = "active";

session_start();
$_SESSION["admin"] = "xojiakbar";
if(isset($_SESSION["post-yaratildi"])){
    $message = $_SESSION["post-yaratildi"];
}
$qoshildi = isset($_SESSION["post-yaratildi"]);
$edited = isset($_SESSION["post-ozgartirildi"]);

$statement = $pdo->prepare("SELECT * FROM posts");
$statement->execute();
$posts = $statement->fetchAll();

if($_SERVER["REQUEST_METHOD"] == "POST" and isset($_POST["DELETE"])){
    $post_id = $_POST["post_id"];
    $statement = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $statement->execute([$post_id]);
    header("Location:blogs.php");
}

require "includes/header.php";
?>

<?php 
  require "includes/footer.php";
?>