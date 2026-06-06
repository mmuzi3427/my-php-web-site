<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require "db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location:index.php");
}

$post_id = $_GET["id"];
$statement = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$statement->execute([$post_id]);
$post = $statement->fetch();


// 🔥 ENG MUHIM QISM — tepaga chiqdi
if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["PUT"])){
    $id = $_POST["post_id"];
    $title = $_POST["title"];
    $body = $_POST["body"];

    if(!empty($title) && !empty($body)){
        $statement = $pdo->prepare("UPDATE posts SET title=:title, body=:body WHERE id = :id");
        $statement->execute([
            "title" => $title,
            "body" => $body,
            "id" => $id,
        ]);
        $_SESSION["post-ozgartirildi"] = 'Post muvaffaqiyatli oʻzgartirildi!!!';
        header("Location: blogs.php");
        exit;  //🔥 juda muhim
    } else {
        echo "Iltimos, barcha maydonlarni to‘ldiring.";
    }
}

$title = "Post yaratish!";
require "includes/header.php";
?>
<style>
    .container {
        margin-top:3rem;
    }
</style>
<form method="POST" action="" class="container">
<input type="hidden" name="PUT">
<input type="hidden" name="post_id" value="<?= $post["id"] ?>">
<h1><?= $post["id"] ?> - ID li post</h1>
<div class="mb-3">
  <label for="exampleFormControlInput1" class="form-label">Post sarlavhasi</label>
  <input name="title" type="text" class="form-control" value="<?=$post["title"]?>">
</div>
<div class="mb-3">
  <label for="exampleFormControlTextarea1" class="form-label">Post matni</label>
  <textarea name="body" class="form-control" rows="3"><?=$post["body"]?></textarea>
</div>

<button type="submit" class="btn btn-outline-success">Postni tahrirlash</button>
</form>

<?php 
  require "includes/footer.php";
?>