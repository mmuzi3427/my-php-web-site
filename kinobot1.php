<?php
error_reporting(0);

// ==========================================
// ASOSIY SOZLAMALAR 
// ==========================================
define('API_KEY', getenv('SIZNING_BOT_TOKENINGIZ'));
define('ADMIN_ID', trim(getenv('SIZNING_ID_RAQAMINGIZ')));
define('BASE_CHANNEL_ID', '-1004425933558'); // Maxfiy kanal ID si
define('URL', getenv('URL')); // WebApp domen manzili

require './db/db.php';

// Telegram API ga so'rov yuborish
function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    return json_decode($res);
}

// Obunani tekshirish funksiyasi
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

// Kino yuborish (protect_content bilan)
function sendMovie($chat_id, $kino_kodi, $pdo) {
    $stmt = $pdo->prepare("SELECT message_id FROM movies WHERE file_code = ?");
    $stmt->execute([$kino_kodi]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($movie) {
        bot('copyMessage', [
            'chat_id' => $chat_id,
            'from_chat_id' => BASE_CHANNEL_ID,
            'message_id' => $movie['message_id'],
            'protect_content' => true // Yuklab olish va boshqalarga yuborishni taqiqlash
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
// CALLBACK QUERY (Tugmalar bosilishi)
// ------------------------------------------
if (isset($update->callback_query)) {
    $cb = $update->callback_query;
    $chat_id = (string)$cb->from->id;
    $data = $cb->data;
    
    // Admin callbacklari
    if ($chat_id === (string)ADMIN_ID) {
        if ($data == "add_channel") {
            $pdo->prepare("UPDATE users SET step = 'add_chan_id' WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "Kanalning ID raqamini (masalan: `-1001234567890`) yuboring:\n\n*Eslatma: Bot o'sha kanalda admin bo'lishi shart!*",
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

    // User "Obunani tekshirish"
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
// MESSAGE (Xabarlar)
// ------------------------------------------
if (isset($update->message)) {
    $message = $update->message;
    $name = $message->from->first_name ?? '';
    $username = $message->from->username ?? '';
    $chat_id = (string)$message->chat->id;
    $text = trim($message->text ?? '');
    $message_id = $message->message_id;

    // Userni olish/ro'yxatdan o'tkazish
    $stmt = $pdo->prepare("SELECT * FROM users WHERE chat_id = ?");
    $stmt->execute([$chat_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
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
    // 1. ADMIN PANEL VA BO'LIMLARI
    // ==========================================
    if ($chat_id === (string)ADMIN_ID) {
        $web_app_url = rtrim(URL, '/') . '/admin_app.php';
        
        $admin_keyboard = json_encode([
            'resize_keyboard' => true,
            'keyboard' => [
                [['text' => "🎬 WebApp Panel", 'web_app' => ['url' => $web_app_url]], ['text' => "➕ Chat orqali kino qo'shish"]],
                [['text' => "📢 Kanallar"], ['text' => "📊 Statistika"]],
                [['text' => "📝 Start xabarini sozlash"]]
            ]
        ]);

        // Panelni ochish / Qaytish
        if ($text == '/panel' || $text == 'Ortga') {
            $pdo->prepare("UPDATE users SET step = 'none', temp_msg_id = NULL WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "👨‍💻 **Boshqaruv paneli:**", 
                'parse_mode' => 'Markdown',
                'reply_markup' => $admin_keyboard
            ]);
            exit();
        }

        // Statistika bo'limi
        if ($text == "📊 Statistika") {
            $u_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $m_count = $pdo->query("SELECT COUNT(*) FROM movies")->fetchColumn();
            $c_count = $pdo->query("SELECT COUNT(*) FROM channels")->fetchColumn();

            $stat_text = "📊 **Bot statistikasi:**\n\n";
            $stat_text .= "👤 **Foydalanuvchilar:** `$u_count` ta\n";
            $stat_text .= "🎬 **Jami kinolar:** `$m_count` ta\n";
            $stat_text .= "📢 **Majburiy kanallar:** `$c_count` ta";

            bot('sendMessage', ['chat_id' => $chat_id, 'text' => $stat_text, 'parse_mode' => 'Markdown']);
            exit();
        }

        // Start xabarini sozlash rejimiga o'tish
        if ($text == "📝 Start xabarini sozlash") {
            $pdo->prepare("UPDATE users SET step = 'set_start_text' WHERE chat_id = ?")->execute([$chat_id]);
            $cancel_btn = json_encode(['resize_keyboard' => true, 'keyboard' => [[['text' => 'Ortga']]]]);
            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "Yangi `/start` matnini yuboring:\n\n*Eslatma: Foydalanuvchi ismini chiqarish uchun `%firstname%` kalit so'zidan foydalaning.*", 
                'parse_mode' => 'Markdown',
                'reply_markup' => $cancel_btn
            ]);
            exit();
        }

        // Start xabarini saqlash
        if ($user_step == 'set_start_text' && !empty($text) && $text != "Ortga") {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('start_text', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$text, $text]);

            $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "✅ Start xabari muvaffaqiyatli saqlandi!", 
                'reply_markup' => $admin_keyboard
            ]);
            exit();
        }

        // Chat orqali kino qo'shish
        if ($text == "➕ Chat orqali kino qo'shish") {
            $pdo->prepare("UPDATE users SET step = 'send_movie_file' WHERE chat_id = ?")->execute([$chat_id]);
            $cancel_btn = json_encode(['resize_keyboard' => true, 'keyboard' => [[['text' => 'Ortga']]]]);
            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "📹 Kinoni (Video yoki Fayl shaklida) botga yuboring:", 
                'reply_markup' => $cancel_btn
            ]);
            exit();
        }

        // Videoni qabul qilish
        if ($user_step == 'send_movie_file' && (isset($message->video) || isset($message->document))) {
            $forwarded = bot('copyMessage', [
                'chat_id' => BASE_CHANNEL_ID,
                'from_chat_id' => $chat_id,
                'message_id' => $message_id
            ]);

            if (isset($forwarded->result->message_id)) {
                $base_msg_id = $forwarded->result->message_id;
                $pdo->prepare("UPDATE users SET step = 'send_movie_code', temp_msg_id = ? WHERE chat_id = ?")->execute([$base_msg_id, $chat_id]);
                
                bot('sendMessage', [
                    'chat_id' => $chat_id, 
                    'text' => "✅ Video saqlandi!\n\nEndi kino uchun **kod (payload)** kiriting (masalan: `101`):", 
                    'parse_mode' => 'Markdown'
                ]);
            } else {
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ Maxfiy kanalga yuklashda xatolik yuz berdi."]);
            }
            exit();
        }

        // Kino kodini saqlash va panelni qaytarish
        if ($user_step == 'send_movie_code' && !empty($text) && $text != "Ortga") {
            if ($temp_msg_id) {
                $stmt = $pdo->prepare("INSERT INTO movies (message_id, file_code) VALUES (?, ?)");
                $stmt->execute([$temp_msg_id, $text]);

                $pdo->prepare("UPDATE users SET step = 'none', temp_msg_id = NULL WHERE chat_id = ?")->execute([$chat_id]);

                bot('sendMessage', [
                    'chat_id' => $chat_id, 
                    'text' => "🎉 **Kino bazaga qo'shildi!**\n\n🎬 **Kino kodi:** `$text`", 
                    'parse_mode' => 'Markdown', 
                    'reply_markup' => $admin_keyboard
                ]);
            }
            exit();
        }

        // Kanallar bo'limi
        if ($text == "📢 Kanallar") {
            $stmt = $pdo->query("SELECT * FROM channels");
            $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $msg = "📢 **Majburiy obuna kanallari:**\n\n";
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

        // Kanal qo'shish bosqichlari
                // 1-bosqich: Kanal ID si kiritilganda
        if ($user_step == 'add_chan_id' && $text != "Ortga") {
            $pdo->prepare("UPDATE users SET step = 'add_chan_title', temp_msg_id = ? WHERE chat_id = ?")->execute([$text, $chat_id]);
            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "Kanal nomini kiriting (masalan: `Obuna bo'lish`):",
                'parse_mode' => 'Markdown'
            ]);
            exit();
        } 
        
        // 2-bosqich: Kanal nomi kiritilganda
        elseif ($user_step == 'add_chan_title' && $text != "Ortga") {
            // Avvalgi ID va yangi Nomni '|||' bilan ajratib saqlaymiz
            $combined_data = $temp_msg_id . "|||" . $text;
            $pdo->prepare("UPDATE users SET step = 'add_chan_url', temp_msg_id = ? WHERE chat_id = ?")->execute([$combined_data, $chat_id]);
            
            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "Kanalga taklif linkini (URL) yuboring (masalan: `https://t.me/bf_va_kinolar`):", 
                'parse_mode' => 'Markdown'
            ]);
            exit();
        }
        
        // 3-bosqich: Kanal URL kiritilganda va Bazaga saqlash
        elseif ($user_step == 'add_chan_url' && $text != "Ortga") {
            // temp_msg_id ichidan ID va Nomni ajratib olamiz
            $data_parts = explode("|||", $temp_msg_id);
            $c_id = trim($data_parts[0] ?? '');
            $c_title = trim($data_parts[1] ?? 'Kanal');

            // Bazaga aniq ID, Nom va URL ni saqlaymiz
            $stmt = $pdo->prepare("INSERT INTO channels (channel_id, channel_title, channel_url) VALUES (?, ?, ?)");
            $stmt->execute([$c_id, $c_title, $text]);
            
            // Step va temp_msg_id ni tozalaymiz
            $pdo->prepare("UPDATE users SET step = 'none', temp_msg_id = NULL WHERE chat_id = ?")->execute([$chat_id]);

            bot('sendMessage', [
                'chat_id' => $chat_id, 
                'text' => "✅ Kanal majburiy obunaga muvaffaqiyatli qo'shildi!\n\n📌 **ID:** `$c_id`\n📌 **Nomi:** $c_title\n📌 **Link:** $text", 
                'parse_mode' => 'Markdown',
                'reply_markup' => $admin_keyboard
            ]);
            exit();
        }
    }

    // ==========================================
    // 2. FOYDALANUVCHILAR UCHUN OBUNA TEKSHIRUVI
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

    // Start matnini olish
    $settings = [];
    $stmt = $pdo->query("SELECT * FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    $start_msg = $settings['start_text'] ?? "🎬 Xush kelibsiz %firstname%! Kino kodi orqali qidiring.";
    $start_msg = str_replace('%firstname%', htmlspecialchars($name), $start_msg);

    // Start buyrug'i
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

    // Oddiy matn yuborilganda kino izlash
    if (!empty($text)) {
        sendMovie($chat_id, $text, $pdo);
    }
}
?>
