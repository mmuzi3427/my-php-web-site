<?php
// ==========================================
// ASOSIY SOZLAMALAR
// ==========================================
define('API_KEY', getenv('SIZNING_BOT_TOKENINGIZ'));
define('ADMIN_ID', getenv('SIZNING_ID_RAQAMINGIZ'));
define('BASE_CHANNEL_ID', '-1004425933558'); // Maxfiy kanal ID si
error_reporting(0);
// MySQL bazaga ulanish ma'lumotlari
require './db/db.php';

// Telegram API so'rov funksiyasi
function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    return json_decode($res);
}

// ==========================================
// MA'LUMOTLARNI QABUL QILISH
// ==========================================
$update = json_decode(file_get_contents('php://input'));
if (isset($update->message)) {
    $message = $update->message;
    $chat_id = $message->chat->id;
    $text = $message->text;
    $message_id = $message->message_id;

    // Foydalanuvchini bazaga qo'shish va holatini (step) aniqlash
    $stmt = $pdo->prepare("SELECT * FROM users WHERE chat_id = ?");
    $stmt->execute([$chat_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $pdo->prepare("INSERT INTO users (chat_id) VALUES (?)")->execute([$chat_id]);
        $user_step = 'none';
        $is_blocked = 0;
    } else {
        $user_step = $user['step'];
        $is_blocked = $user['is_blocked'];
    }

    // Agar bloklangan bo'lsa, hech narsa qilmaslik
    if ($is_blocked == 1 && $chat_id != ADMIN_ID) {
        exit();
    }

    // Bot sozlamalarini bazadan olish
    $settings = [];
    $stmt = $pdo->query("SELECT * FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    $protect_content = ($settings['protect_content'] == '1') ? true : false;

    // ==========================================
    // FOYDALANUVCHI QISMI
    // ==========================================
    if (strpos($text, '/start') === 0) {
        $explode = explode(' ', $text);
        
        if (count($explode) == 1) {
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $settings['start_text']
            ]);
        } elseif (count($explode) == 2) {
            $kino_kodi = $explode[1];
            
            // Kod bo'yicha kinoni bazadan qidirish
            $stmt = $pdo->prepare("SELECT message_id FROM movies WHERE file_code = ?");
            $stmt->execute([$kino_kodi]);
            $movie = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($movie) {
                // Kinoni maxfiy kanaldan foydalanuvchiga nusxalash
                bot('copyMessage', [
                    'chat_id' => $chat_id,
                    'from_chat_id' => BASE_CHANNEL_ID,
                    'message_id' => $movie['message_id'],
                    'protect_content' => $protect_content
                ]);
            } else {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "❌ Kechirasiz, bu kino topilmadi yoki o'chirilgan."
                ]);
            }
        }
        // Foydalanuvchi step'ini tozalash
        $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
    }

    // ==========================================
    // ADMIN PANEL QISMI
    // ==========================================
    if ($chat_id == ADMIN_ID) {
        
        $admin_keyboard = json_encode([
            'resize_keyboard' => true,
            'keyboard' => [
                [
                    // Oddiy tugma o'rniga Web App tugmasi
                    ['text' => "🎬 Kino Ko'chirish", 'web_app' => ['url' => 'https://my-php-web-site.onrender.com/admin_app.php']], 
                    ['text' => "🎬 Kino yuklash"],
                    ['text' => "📊 Statistika"]
                ],
                [['text' => "⚙️ Sozlamalar"], ['text' => "📢 Kanallar"]],
                [['text' => "📝 Start xabarini sozlash"]]
            ]
        ]);


        if ($text == '/panel' || $text == 'Ortga') {
            $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "👨‍💻 Boshqaruv paneli:",
                'reply_markup' => $admin_keyboard
            ]);
        }
                // --- START XABARINI SOZLASH BO'LIMI ---
        if ($text == "📝 Start xabarini sozlash") {
            // Admin holatini 'set_start_text' ga o'zgartiramiz
            $pdo->prepare("UPDATE users SET step = 'set_start_text' WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Yangi start xabarini yuboring (Masalan: 🎬 Botimizga xush kelibsiz!):",
                'reply_markup' => json_encode([
                    'resize_keyboard' => true,
                    'keyboard' => [[['text' => "Ortga"]]]
                ])
            ]);
        }

        // Agar admin 'set_start_text' holatida matn yuborsa
        if ($user_step == 'set_start_text' && $text != "Ortga" && $text != "/panel") {
            // Bazadagi start xabarini yangilaymiz
            $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'start_text'")->execute([$text]);
            // Admin holatini tozalaymiz
            $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "✅ Start xabari muvaffaqiyatli o'zgartirildi!",
                'reply_markup' => $admin_keyboard
            ]);
        }

        // --- SOZLAMALAR (KONTENTNI HIMOYALASH) BO'LIMI ---
        if ($text == "⚙️ Sozlamalar") {
            $pdo->prepare("UPDATE users SET step = 'set_protection' WHERE chat_id = ?")->execute([$chat_id]);
            $holat = $protect_content ? "YOQILGAN 🟢" : "O'CHIRILGAN 🔴";
            
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⚙️ **Kino himoyasi (Ulashish va saqlashni taqiqlash)**\n\nHozirgi holat: $holat\n\nO'zgartirish uchun pastdagi tugmalardan birini tanlang:",
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode([
                    'resize_keyboard' => true,
                    'keyboard' => [
                        [['text' => "Himoyani yoqish 🟢"], ['text' => "Himoyani o'chirish 🔴"]],
                        [['text' => "Ortga"]]
                    ]
                ])
            ]);
        }

        if ($user_step == 'set_protection') {
            if ($text == "Himoyani yoqish 🟢") {
                $pdo->prepare("UPDATE settings SET setting_value = '1' WHERE setting_key = 'protect_content'")->execute();
                $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ Himoya yoqildi! Endi kinolarni birovga forward qilib bo'lmaydi.", 'reply_markup' => $admin_keyboard]);
            }
            if ($text == "Himoyani o'chirish 🔴") {
                $pdo->prepare("UPDATE settings SET setting_value = '0' WHERE setting_key = 'protect_content'")->execute();
                $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ Himoya o'chirildi! Kinolarni bemalol forward qilish mumkin.", 'reply_markup' => $admin_keyboard]);
            }
        }


        // --- STATISTIKA BO'LIMI ---
        if ($text == "📊 Statistika") {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $users_count = $stmt->fetch()['count'];
            
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM movies");
            $movies_count = $stmt->fetch()['count'];

            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "📊 **Bot statistikasi:**\n\n👥 Foydalanuvchilar: $users_count ta\n🎬 Yuklangan kinolar: $movies_count ta",
                'parse_mode' => 'Markdown'
            ]);
        }

        // --- KINO YUKLASH BO'LIMI ---
        if ($text == "🎬 Kino yuklash") {
            $pdo->prepare("UPDATE users SET step = 'upload_movie' WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "📥 Menga videoni yoki faylni yuboring (Men uni avtomatik ravishda maxfiy kanalga yuklayman va linkini beraman):",
                'reply_markup' => json_encode([
                    'resize_keyboard' => true,
                    'keyboard' => [[['text' => "Ortga"]]]
                ])
            ]);
        }

        // Agar admin "upload_movie" rejimida bo'lsa va fayl yuborsa
        if ($user_step == 'upload_movie') {
            
            // 1. Faylni maxfiy kanalga nusxalash (copyMessage orqali toza qilib yuborish)
            $send = bot('copyMessage', [
                'chat_id' => BASE_CHANNEL_ID,
                'from_chat_id' => $chat_id,
                'message_id' => $message_id
            ]);

            if (isset($send->result->message_id)) {
                $channel_msg_id = $send->result->message_id;
                
                // 2. Tasodifiy qisqa kod yaratish (masalan: _A1B2C3D4)
                $kino_kodi = "_" . strtoupper(substr(md5(time() . rand(1, 10000)), 0, 10));
                
                // 3. Bazaga saqlash
                $stmt = $pdo->prepare("INSERT INTO movies (file_code, message_id) VALUES (?, ?)");
                $stmt->execute([$kino_kodi, $channel_msg_id]);

                $bot_username = bot("getme")->result->username; // O'zingizning botingiz userini yozing
                $link = "https://t.me/$bot_username?start=$kino_kodi";

                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "✅ Kino muvaffaqiyatli bazaga va maxfiy kanalga saqlandi!\n\n🔗 Mana yuklab olish linki:\n`$link`",
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $admin_keyboard
                ]);
                
                // Stepni tozalash
                $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
            } else {
                bot('sendMessage', [
                    'chat_id' => $chat_id,
                    'text' => "❌ Faylni maxfiy kanalga yuborishda xatolik yuz berdi. Botingiz kanalga admin ekanligini tekshiring."
                ]);
            }
        }
        
        // Qo'shimcha sozlamalar (Masalan, Himoyani yoqish/o'chirish) shu yerda yoziladi...
    }
}
?>
