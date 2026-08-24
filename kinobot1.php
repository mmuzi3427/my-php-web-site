<?php
error_reporting(0);

// ==========================================
// ASOSIY SOZLAMALAR 
// ==========================================
define('API_KEY', getenv('SIZNING_BOT_TOKENINGIZ'));
define('ADMIN_ID', trim(getenv('SIZNING_ID_RAQAMINGIZ')));
define('BASE_CHANNEL_ID', '-1004425933558'); // Maxfiy kanal ID si

require './db/db.php';

function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    return json_decode($res);
}

function checkSub($user_id, $pdo) {
    if ((string)$user_id === (string)ADMIN_ID) return [];

    $stmt = $pdo->query("SELECT * FROM channels");
    $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $not_subscribed = [];

    foreach ($channels as $ch) {
        $res = bot('getChatMember', [
            'chat_id' => $ch['channel_id'],
            'user_id' => $user_id
        ]);

        $status = $res->result->status ?? 'left';

        if (in_array($status, ['left', 'kicked'])) {
            $not_subscribed[] = [
                'title' => $ch['channel_title'],
                'url' => $ch['channel_url']
            ];
        }
    }

    return $not_subscribed;
}

function sendMovie($chat_id, $kino_kodi, $pdo) {
    $stmt = $pdo->prepare("SELECT message_id FROM movies WHERE file_code = ?");
    $stmt->execute([$kino_kodi]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($movie) {
        bot('copyMessage', [
            'chat_id' => $chat_id,
            'from_chat_id' => BASE_CHANNEL_ID,
            'message_id' => $movie['message_id']
        ]);
    } else {
        bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ Kino topilmadi!"]);
    }
}

// ==========================================
// MA'LUMOTLARNI QABUL QILISH
// ==========================================
$update = json_decode(file_get_contents('php://input'));

// ------------------------------------------
// CALLBACK QUERY
// ------------------------------------------
if (isset($update->callback_query)) {
    $cb = $update->callback_query;
    $chat_id = (string)$cb->from->id;
    $data = $cb->data;
    
    if ($chat_id === (string)ADMIN_ID) {
        if ($data == "add_channel") {
            $pdo->prepare("UPDATE users SET step = 'add_chan_id' WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Kanalning ID raqamini (masalan: `-1001234567890`) yuboring:",
                'parse_mode' => 'Markdown'
            ]);
            exit();
        }

        if (strpos($data, 'del_chan_') === 0) {
            $chan_id = str_replace('del_chan_', '', $data);
            $pdo->prepare("DELETE FROM channels WHERE id = ?")->execute([$chan_id]);
            bot('answerCallbackQuery', ['callback_query_id' => $cb->id, 'text' => "✅ Kanal o'chirildi!"]);
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $cb->message->message_id]);
            exit();
        }
    }

    if (strpos($data, 'check_sub_') === 0) {
        $kino_kodi = str_replace('check_sub_', '', $data);
        $unsubscribed = checkSub($chat_id, $pdo);

        if (empty($unsubscribed)) {
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $cb->message->message_id]);
            
            if ($kino_kodi != 'none' && !empty($kino_kodi)) {
                sendMovie($chat_id, $kino_kodi, $pdo);
            } else {
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ Obuna tasdiqlandi! Endi kino kodini yuborishingiz mumkin."]);
            }
        } else {
            bot('answerCallbackQuery', [
                'callback_query_id' => $cb->id,
                'text' => "⚠️ Hali barcha kanallarga obuna bo'lmadingiz!",
                'show_alert' => true
            ]);
        }
    }
    exit();
}

// ------------------------------------------
// MESSAGE
// ------------------------------------------
if (isset($update->message)) {
    $message = $update->message;
    $name = $message->from->first_name ?? '';
    $username = $message->from->username ?? '';
    $chat_id = (string)$message->chat->id;
    $text = trim($message->text ?? '');
    $message_id = $message->message_id;

    $stmt = $pdo->prepare("SELECT * FROM users WHERE chat_id = ?");
    $stmt->execute([$chat_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        bot('sendMessage', [
            'chat_id' => ADMIN_ID,
            'text' => "🆕 <b>Yangi foydalanuvchi:</b>\n👤 <b>Ismi:</b> $name\n📧 <b>Useri:</b> @$username\n🆔 <b>ID raqami:</b> <code>$chat_id</code>",
            'reply_markup' => json_encode([
                'inline_keyboard' => [[['text' => "👀 Koʻrish", 'url' => "tg://user?id=$chat_id"]]]
            ]),
            'parse_mode' => "html"
        ]);
        $pdo->prepare("INSERT INTO users (chat_id, step) VALUES (?, 'none')")->execute([$chat_id]);
        $user_step = 'none';
        $temp_msg_id = null;
        $is_blocked = 0;
    } else {
        $user_step = $user['step'];
        $temp_msg_id = $user['temp_msg_id'] ?? null;
        $is_blocked = $user['is_blocked'] ?? 0;
    }

    if ($is_blocked == 1 && $chat_id !== (string)ADMIN_ID) exit();

    // ==========================================
    // 1. ADMIN PANEL MANTIQLARI
    // ==========================================
    if ($chat_id === (string)ADMIN_ID) {
        $admin_keyboard = json_encode([
            'resize_keyboard' => true,
            'keyboard' => [
                [['text' => "📤 Kino yuklash"]],
                [['text' => "📢 Kanallar"], ['text' => "📊 Statistika"]],
                [['text' => "⚙️ Sozlamalar"], ['text' => "📝 Start xabarini sozlash"]]
            ]
        ]);

        // Ortga qaytish yoki Panel menyusi
        if ($text == '/panel' || $text == 'Ortga') {
            $pdo->prepare("UPDATE users SET step = 'none', temp_msg_id = NULL WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "👨‍💻 Boshqaruv paneli:", 
                'reply_markup' => $admin_keyboard
            ]);
            exit();
        }

        // 1-bosqich: Chat orqali kino qo'shish tugmasi bosilishi
        if ($text == "📤 Kino yuklash") {
            $pdo->prepare("UPDATE users SET step = 'send_movie_file' WHERE chat_id = ?")->execute([$chat_id]);
            $cancel_btn = json_encode(['resize_keyboard' => true, 'keyboard' => [[['text' => 'Ortga']]]]);
            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "📹 Kinoni (Video yoki Fayl shaklida) botga yuboring:", 
                'reply_markup' => $cancel_btn
            ]);
            exit();
        }

        // 2-bosqich: Admin videoni yuborganda
        if ($user_step == 'send_movie_file' && (isset($message->video) || isset($message->document))) {
            $forwarded = bot('copyMessage', [
                'chat_id' => BASE_CHANNEL_ID,
                'from_chat_id' => $chat_id,
                'message_id' => $message_id
            ]);

            if (isset($forwarded->result->message_id)) {
                $base_msg_id = $forwarded->result->message_id;
                
                // Maxfiy kanaldagi ID raqamini bazaga saqlaymiz
                $pdo->prepare("UPDATE users SET step = 'send_movie_code', temp_msg_id = ? WHERE chat_id = ?")
                    ->execute([$base_msg_id, $chat_id]);
                
                bot('sendMessage', [
                    'chat_id' => $chat_id, 
                    'text' => "✅ Video maxfiy kanalga saqlandi!\n\nEndi ushbu kino uchun **Start kodini (payload)** kiriting (masalan: `kino123`):", 
                    'parse_mode' => 'Markdown'
                ]);
            } else {
                bot('sendMessage', [
                    'chat_id' => $chat_id, 
                    'text' => "❌ Videoni maxfiy kanalga yuklashda xatolik! Bot maxfiy kanalda admin ekanligini va BASE_CHANNEL_ID to'g'riligini tekshiring."
                ]);
            }
            exit();
        }

        // 3-bosqich: Admin kino kodini kiritganda
        if ($user_step == 'send_movie_code' && !empty($text) && $text != "Ortga") {
            if ($temp_msg_id) {
                // Kinoni saqlash
                $stmt = $pdo->prepare("INSERT INTO movies (message_id, file_code) VALUES (?, ?)");
                $stmt->execute([$temp_msg_id, $text]);

                // Step va vaqtinchalik ID ni tozalash
                $pdo->prepare("UPDATE users SET step = 'none', temp_msg_id = NULL WHERE chat_id = ?")->execute([$chat_id]);

                // Natijani xabar qilish va ADMIN PANELNI QAYTARIЅH
                bot('sendMessage', [
                    'chat_id' => $chat_id, 
                    'text' => "🎉 Kino bazaga muvaffaqiyatli qo'shildi!\n\n🎬 Kino kodi: `$text`", 
                    'parse_mode' => 'Markdown', 
                    'reply_markup' => $admin_keyboard
                ]);
            } else {
                bot('sendMessage', [
                    'chat_id' => $chat_id, 
                    'text' => "❌ Xatolik yuz berdi: Video topilmadi. Qaytadan urinib ko'ring.", 
                    'reply_markup' => $admin_keyboard
                ]);
                $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
            }
            exit();
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
            exit();
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
            exit();
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
            exit();
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
            exit();
            
}

        // Kanallar bo'limi
        if ($text == "📢 Kanallar") {
            $stmt = $pdo->query("SELECT * FROM channels");
            $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $msg = "📢 **Majburiy obuna kanallari ro'yxati:**\n\n";
            $buttons = [];
            
            foreach ($channels as $ch) {
                $msg .= "🔹 {$ch['channel_title']} (`{$ch['channel_id']}`)\n";
                $buttons[] = [['text' => "❌ O'chirish: " . $ch['channel_title'], 'callback_data' => "del_chan_" . $ch['id']]];
            }

            $buttons[] = [['text' => "➕ Yangi kanal qo'shish", 'callback_data' => "add_channel"]];

            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => $msg,
                'parse_mode' => 'Markdown',
                'reply_markup' => json_encode(['inline_keyboard' => $buttons])
            ]);
            exit();
        }

        // Kanal qo'shish jarayonlari
        if ($user_step == 'add_chan_id' && $text != "Ortga") {
            $pdo->prepare("UPDATE users SET step = 'add_chan_title', temp_msg_id = ? WHERE chat_id = ?")->execute([$text, $chat_id]);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Kanal nomini kiriting (Tugmada ko'rinadigan matn):"]);
            exit();
        } 
        elseif ($user_step == 'add_chan_title' && $text != "Ortga") {
            $pdo->prepare("UPDATE users SET step = 'add_chan_url' WHERE chat_id = ?")->execute([$chat_id]);
            // Vaqtinchalik sarlavhani saqlash
            $pdo->prepare("UPDATE channels SET channel_title = ? WHERE channel_id = ?")->execute([$text, $temp_msg_id]);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "Kanalga taklif linkini (URL) yuboring (Masalan: `https://t.me/kanal_nomi`):", 'parse_mode' => 'Markdown']);
            exit();
        }
        elseif ($user_step == 'add_chan_url' && $text != "Ortga") {
            $stmt = $pdo->prepare("INSERT INTO channels (channel_id, channel_title, channel_url) VALUES (?, 'Kanal', ?)");
            $stmt->execute([$temp_msg_id, $text]);
            
            $pdo->prepare("UPDATE users SET step = 'none', temp_msg_id = NULL WHERE chat_id = ?")->execute([$chat_id]);

            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "✅ Kanal majburiy obunaga muvaffaqiyatli qo'shildi!", 'reply_markup' => $admin_keyboard]);
            exit();
        }
    }

    // ==========================================
    // 2. ODDIY FOYDALANUVCHILAR UCHUN OBUNA TEKSHIRUV
    // ==========================================
    $unsubscribed = checkSub($chat_id, $pdo);

    if (!empty($unsubscribed)) {
        $buttons = [];
        foreach ($unsubscribed as $ch) {
            $buttons[] = [['text' => "➕ " . $ch['title'], 'url' => $ch['url']]];
        }

        $code_param = 'none';
        if (strpos($text, '/start') === 0) {
            $explode = explode(' ', $text);
            if (isset($explode[1])) $code_param = $explode[1];
        } else {
            $code_param = $text;
        }

        $buttons[] = [['text' => "🔄 Obunani tekshirish", 'callback_data' => "check_sub_$code_param"]];

        bot('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "⚠️ Botdan foydalanish uchun quyidagi kanallarga obuna bo'ling:",
            'reply_markup' => json_encode(['inline_keyboard' => $buttons])
        ]);
        exit();
    }

    // Bot sozlamalari va /start
    $settings = [];
    $stmt = $pdo->query("SELECT * FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    $start_msg = $settings['start_text'] ?? "🎬 Xush kelibsiz %firstname%! Kino kodi orqali qidiring.";
    $start_msg = str_replace('%firstname%', htmlspecialchars($name), $start_msg);

    if (strpos($text, '/start') === 0) {
        $explode = explode(' ', $text);
        $kino_kodi = $explode[1] ?? 'none';

        if ($kino_kodi == 'none') {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => $start_msg, 'parse_mode' => 'html']);
        } else {
            sendMovie($chat_id, $kino_kodi, $pdo);
        }
        exit();
    }

    // Oddiy kino kodi yozilganda
    if (!empty($text)) {
        sendMovie($chat_id, $text, $pdo);
    }
}
?>
