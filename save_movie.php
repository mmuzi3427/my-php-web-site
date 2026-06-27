<?php
// Baza ulanish sozlamalari (O'zingiznikini yozing)
require './db/db.php';

// Formadan kelayotgan ma'lumotlarni tekshirish
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_id']) && isset($_POST['file_code'])) {
    
    $message_id = intval($_POST['message_id']);
    $file_code = trim($_POST['file_code']); // Eski yoki yangi start kodi

    if (!empty($file_code) && $message_id > 0) {
        try {
            // Bazaga ma'lumotni qo'shish (Agar kod avval mavjud bo'lsa IGNORE qiladi yoki xato beradi)
            $stmt = $pdo->prepare("INSERT INTO movies (file_code, message_id) VALUES (?, ?)");
            $stmt->execute([$file_code, $message_id]);
            
            echo "<script>
                    alert('✅ Kino muvaffaqiyatli saqlandi!');
                    window.location.href = 'admin_app.php';
                  </script>";
        } catch (PDOException $e) {
            // Agar bir xil kod ikki marta kiritilsa xato chiqadi
            echo "<script>
                    alert('❌ Xatolik: Bu start kodi bazada allaqachon mavjud!');
                    window.location.href = 'admin_app.php';
                  </script>";
        }
    } else {
        echo "Ma'lumotlar to'liq emas!";
    }
} else {
    echo "Ruxsat berilmagan so'rov!";
}
?>
