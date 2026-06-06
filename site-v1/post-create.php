<?php
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require "db.php";
session_start();

if(!isset($_SESSION['admin'])){
    header("Location:index.php");
}


if($_SERVER["REQUEST_METHOD"]=="POST"){
    $title = $_POST["title"];
    $body = $_POST["body"];

    if(!empty($title) && !empty($body)){
        $statement = $pdo->prepare("INSERT INTO posts (title, body) VALUES (:title, :body)");
        $statement->execute([
            "title" => $title,
            "body" => $body
        ]);
        $_SESSION["post-yaratildi"] = 'Post muvaffaqiyatli yaratildi!!!';
        header("Location: blogs.php");
        //exit;  🔥 juda muhim
    } else {
        $error = "Iltimos, barcha maydonlarni to‘ldiring.";
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
<div class="mb-3">
  <label for="exampleFormControlInput1" class="form-label">Post sarlavhasi</label>
  <input name="title" type="text" class="form-control">
</div>
<div class="mb-3">
  <label for="exampleFormControlTextarea1" class="form-label">Post matni</label>
  <textarea name="body" class="form-control" rows="3"></textarea>
</div>

<button type="submit" class="btn btn-outline-success">Post yuborish</button>
</form>

<?php 
  require "includes/footer.php";
?>