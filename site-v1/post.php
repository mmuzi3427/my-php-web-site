<?php
$id = $_GET["id"];
require "db.php";
$statement = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$statement->execute([$id]);
$post = $statement->fetch();
#var_dump($post);
$title = "Post";
require "includes/header.php";

?>

<div class="container" style="margin-top:3rem;">
    <h1><?= $post["title"] ?></h1>
    <p class="fs-5 col-md-8"><?= $post["body"] ?></p>
    <p style="width:100%"><?= $post["created_at"] ?></p>
    <hr class="col-3 col-md-2 mb-5">
</div>

<?php 
  require "includes/footer.php";
?>