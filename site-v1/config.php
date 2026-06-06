<?php
session_start();

date_default_timezone_set('Asia/Tashkent');

$conn = new mysqli(
"sql107.infinityfree.com",
"if0_41240747",
"wiNgwPtwYG",
"if0_41240747_users"
);

if($conn->connect_error){
die("DB error");
}
?>