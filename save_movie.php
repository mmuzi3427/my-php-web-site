<?php
// Baza ulanish sozlamalari (O'zingiznikini yozing)
require './db/db.php';
// 1. MAXFIY PAROLNI O'RNATING (Buni hech kimga aytmang)
define('SECRET_PASSWORD', getenv('SECRET_PASSWORD')); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Parolni tekshirish
    $input_password = $_POST['admin_password'] ?? '';
    if ($input_password !== SECRET_PASSWORD) {
        echo "<script>
                alert('❌ Xato: Maxfiy parol noto\'g\'ri! Ma\'lumotlar rad etildi.');
                window.location.href = 'admin_app.php';
              </script>";
        exit();
    }

    // Kinolar massivini qabul qilish
    $movies = $_POST['movies'] ?? [];
    $success_count = 0;
    $error_count = 0;

    if (!empty($movies)) {
        // Tranzaksiyani boshlaymiz (bazaga tezroq va xavfsiz yozish uchun)
        $pdo->beginTransaction();
        
        try {
            $stmt = $pdo->prepare("INSERT INTO movies (file_code, message_id) VALUES (?, ?)");
            
            foreach ($movies as $movie) {
                $message_id = intval($movie['message_id']);
                $file_code = trim($movie['file_code']);

                // Agar ikkala maydon ham to'ldirilgan bo'lsagina bazaga yozadi
                if (!empty($file_code) && $message_id > 0) {
                    $stmt->execute([$file_code, $message_id]);
                    $success_count++;
                }
            }
            
            // Hamma jarayon muvaffaqiyatli bo'lsa bazaga tasdiqlaymiz
            $pdo->commit();
            
            if ($success_count > 0) {
                echo "<script>
                        alert('✅ Muvaffaqiyatli yakunlandi! \\n Safarbar etilgan kinolar: $success_count ta.');
                        window.location.href = 'admin_app.php';
                      </script>";
            } else {
                echo "<script>
                        alert('⚠️ Hech qanday ma\'lumot kiritilmadi (Qatorlar bo\'sh edi).');
                        window.location.href = 'admin_app.php';
                      </script>";
            }

        } catch (PDOException $e) {
            // Agar biror xato bo'lsa (masalan dublikat kod) hammasini bekor qilamiz
            $pdo->rollBack();
            echo "<script>
                    alert('❌ Xatolik yuz berdi: Ehtimol siz kiritgan start kodlaridan biri bazada allaqachon bordir!');
                    window.location.href = 'admin_app.php';
                  </script>";
        }
    }
} else {
    echo "Ruxsat berilmagan so'rov!";
}

?>
