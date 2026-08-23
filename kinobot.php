<?php
// ================
// ALPHA coder
// ================
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
function checkSub($user_id, $pdo) {
    $stmt = $pdo->query("SELECT * FROM channels");
    $channels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $not_subscribed = [];

    foreach ($channels as $ch) {
        $res = bot('getChatMember', [
            'chat_id' => $ch['channel_id'],
            'user_id' => $user_id
        ]);

        $status = $res->result->status ?? 'left';

        // Agar foydalanuvchi kanalda bo'lmasa yoki chiqib ketgan bo'lsa
        if (in_array($status, ['left', 'kicked'])) {
            $not_subscribed[] = [
                'title' => $ch['channel_title'],
                'url' => $ch['channel_url']
            ];
        }
    }

    return $not_subscribed;
}
// ==========================================
// MA'LUMOTLARNI QABUL QILISH
// ==========================================
$update = json_decode(file_get_contents('php://input'));
if (isset($update->callback_query)) {
    $cb = $update->callback_query;
    $chat_id = $cb->from->id;
    $data = $cb->data;
    
    if (strpos($data, 'check_sub_') === 0) {
        $kino_kodi = str_replace('check_sub_', '', $data);
        $unsubscribed = checkSub($chat_id, $pdo);

        if (empty($unsubscribed)) {
            bot('deleteMessage', ['chat_id' => $chat_id, 'message_id' => $cb->message->message_id]);
            
            // Obuna bo'lingan bo'lsa kinoni yuboramiz
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
if (isset($update->message)) {
    $message = $update->message;
    $name = $message->from->first_name;
    $username = $message->from->username;
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
        bot('sendMessage', [
                'chat_id' => ADMIN_ID,
                'text' => "🆕 <b>Yangi foydalanuvchi:</b>\n👤 <b>Ismi:</b> $name\n📧 <b>Useri:</b> $username\n🆔 <b>ID raqami:</b> <code>$chat_id</code>",
                'reply_markup' => json_encode([
                        'inline_keyboard' => [
                                [
                                        ['text' => "👀 Koʻrish", 'url' => "tg://user?id=$chat_id"]
                                ]
                        ]
                ]),
                'parse_mode' => "html"
            ]);
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
    $settings['start_text'] = str_replace('%firstname%', $name , $settings['start_text']);
    
    // ==========================================
    // FOYDALANUVCHI QISMI
    // ==========================================
    if (strpos($text, '/start') === 0) {
        $explode = explode(' ', $text);
        $kino_kodi = $explode[1] ?? 'none';

        // Obunani tekshirish
        $unsubscribed = checkSub($chat_id, $pdo);

        if (!empty($unsubscribed) && $chat_id != ADMIN_ID) {
            $buttons = [];
            foreach ($unsubscribed as $ch) {
                $buttons[] = [['text' => "➕ " . $ch['title'], 'url' => $ch['url']]];
            }
            // Tekshirish tugmasi
            $buttons[] = [['text' => "🔄 Obunani tekshirish", 'callback_data' => "check_sub_$kino_kodi"]];

            bot('sendMessage', [
                'chat_id' => $chat_id,
                'text' => "⚠️ Botdan foydalanish uchun quyidagi kanallarga obuna bo'ling:",
                'reply_markup' => json_encode(['inline_keyboard' => $buttons])
            ]);
            exit();
        }

        // Obuna bo'lgan bo'lsa yoki start oddiy bo'lsa
        if ($kino_kodi == 'none') {
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "🎬 Xush kelibsiz! Kino kodi orqali qidiring."]);
        } else {
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
                bot('sendMessage', ['chat_id' => $chat_id, 'text' => "❌ Kino topilmadi."]);
            }
        }
    }

    // ================= ADMIN PANEL =================
    if ($chat_id == ADMIN_ID) {
        $admin_keyboard = json_encode([
            'resize_keyboard' => true,
            'keyboard' => [
                [['text' => "🎬 Kino yuklash / Ko'chirish", 'web_app' => ['url' => 'https://SIZNING_DOMENINGIZ.uz/admin_app.php']], ['text' => "📊 Statistika"]],
                [['text' => "📢 Kanallar"], ['text' => "⚙️ Sozlamalar"]],
                [['text' => "📝 Start xabarini sozlash"]]
            ]
        ]);

        if ($text == '/panel' || $text == 'Ortga') {
            $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([$chat_id]);
            bot('sendMessage', ['chat_id' => $chat_id, 'text' => "👨‍💻 Boshqaruv paneli:", 'reply_markup' => $admin_keyboard]);
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
        }
    }
}

// Callback so'rovlari (Admin uchun kanallarni o'chirish/qo'shish)
if (isset($update->callback_query) && $update->callback_query->from->id == ADMIN_ID) {
    $cb = $update->callback_query;
    $data = $cb->data;

    if ($data == "add_channel") {
        $pdo->prepare("UPDATE users SET step = 'add_chan_id' WHERE chat_id = ?")->execute([ADMIN_ID]);
        bot('sendMessage', [
            'chat_id' => ADMIN_ID,
            'text' => "Kanalning ID raqamini (masalan: `-1001234567890`) yoki ulanish linkini yuboring:\n\n*Eslatma: Bot o'sha kanalda admin bo'lishi shart!*",
            'parse_mode' => 'Markdown'
        ]);
    }

    if (strpos($data, 'del_chan_') === 0) {
        $chan_id = str_replace('del_chan_', '', $data);
        $pdo->prepare("DELETE FROM channels WHERE id = ?")->execute([$chan_id]);
        bot('answerCallbackQuery', ['callback_query_id' => $cb->id, 'text' => "✅ Kanal o'chirildi!"]);
        bot('deleteMessage', ['chat_id' => ADMIN_ID, 'message_id' => $cb->message->message_id]);
    }
}

// Admin text kiritganda kanal qo'shish bosqichlari
if (isset($message) && $chat_id == ADMIN_ID) {
    if ($user_step == 'add_chan_id' && $text != "Ortga") {
        $pdo->prepare("UPDATE users SET step = 'add_chan_title' WHERE chat_id = ?")->execute([ADMIN_ID]);
        file_put_contents("temp_chan_$ADMIN_ID.json", json_encode(['chan_id' => $text]));
        
        bot('sendMessage', ['chat_id' => ADMIN_ID, 'text' => "Kanal nomini kiriting (Tugmada ko'rinadigan matn):"]);
    } 
    elseif ($user_step == 'add_chan_title' && $text != "Ortga") {
        $temp = json_decode(file_get_contents("temp_chan_$ADMIN_ID.json"), true);
        $temp['title'] = $text;
        file_put_contents("temp_chan_$ADMIN_ID.json", json_encode($temp));
        
        $pdo->prepare("UPDATE users SET step = 'add_chan_url' WHERE chat_id = ?")->execute([ADMIN_ID]);
        bot('sendMessage', ['chat_id' => ADMIN_ID, 'text' => "Kanalga taklif linkini (URL) yuboring (Masalan: `https://t.me/kanal_nomi`):", 'parse_mode' => 'Markdown']);
    }
    elseif ($user_step == 'add_chan_url' && $text != "Ortga") {
        $temp = json_decode(file_get_contents("temp_chan_$ADMIN_ID.json"), true);
        
        $stmt = $pdo->prepare("INSERT INTO channels (channel_id, channel_title, channel_url) VALUES (?, ?, ?)");
        $stmt->execute([$temp['chan_id'], $temp['title'], $text]);
        
        unlink("temp_chan_$ADMIN_ID.json");
        $pdo->prepare("UPDATE users SET step = 'none' WHERE chat_id = ?")->execute([ADMIN_ID]);

        bot('sendMessage', ['chat_id' => ADMIN_ID, 'text' => "✅ Kanal majburiy obunaga muvaffaqiyatli qo'shildi!"]);
    }
    
}

?>
