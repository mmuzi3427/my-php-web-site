<?php

define('TOKEN', getenv("TOKEN"));

// Webhook barqarorligi va xavfsizlik uchun xatoliklarni ekranga chiqarmaymiz
error_reporting(0);

// Telegram API bilan ishlash uchun asosiy funksiya
function bot($method, $datas = []) {
    $url = "https://api.telegram.org/bot" . TOKEN . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    // Tashqi SSL sertifikat muammolarini chetlab o'tish (Render uchun muhim)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $res = curl_exec($ch);
    curl_close($ch);
    return json_decode($res);
}

// Telegram serveridan kelgan JSON ma'lumotni o'qish
$update = json_decode(file_get_contents('php://input'));

// Agar brauzerdan kirilsa, kod ishlashni to'xtatadi va xato bermaydi
if (!$update) {
    die("Bot muvaffaqiyatli ishlamoqda. Uni Telegram orqali sinab ko'ring!");
}

// Viloyatlar ro'yxati va tugmalari (Inline Keyboard)
$regions_keyboard = json_encode([
    'inline_keyboard' => [
        [['text' => "🕐 Xiva", 'callback_data' => "time=Xiva"], ['text' => "🕑 Nukus", 'callback_data' => "time=Nukus"]],
        [['text' => "🕒 Qarshi", 'callback_data' => "time=Qarshi"], ['text' => "🕓 Jizzax", 'callback_data' => "time=Jizzax"]],
        [['text' => "🕔 Navoiy", 'callback_data' => "time=Navoiy"], ['text' => "🕕 Buxoro", 'callback_data' => "time=Buxoro"]],
        [['text' => "🕖 Andijon", 'callback_data' => "time=Andijon"], ['text' => "🕝 Guliston", 'callback_data' => "time=Guliston"]],
        [['text' => "🕗 Urganch", 'callback_data' => "time=Urganch"], ['text' => "九 Farg'ona", 'callback_data' => "time=Farg'ona"]],
        [['text' => "🕙 Toshkent", 'callback_data' => "time=Toshkent"], ['text' => "🕚 Zarafshon", 'callback_data' => "time=Zarafshon"]],
        [['text' => "🕛 Namangan", 'callback_data' => "time=Namangan"], ['text' => "🕜 Samarqand", 'callback_data' => "time=Samarqand"]],
    ]
]);

$start_caption = "<b>☪ Assalomu alaykum xurmatli foydalanuvchi, botimizga xush kelibsiz!</b>\n\n<i>'Namozni to'kis ado etinglar. Albatta, namoz mo'minlarga vaqtida farz qilingandir'</i>\n<b>Niso surasi, 103-oyat</b>";

// --------------------------------------------------------------------
// 1. CHAT XABARLARINI QABUL QILISH (TEXT)
// --------------------------------------------------------------------
if (isset($update->message)) {
    $message = $update->message;
    $cid = $message->chat->id;
    $tx = $message->text;

    if ($tx == "/start") {
        bot('sendphoto', [
            'chat_id' => $cid,
            'photo' => "https://t.me/botim1chi/450",
            'caption' => $start_caption,
            'parse_mode' => 'html',
            'reply_markup' => $regions_keyboard
        ]);
    }
}

// --------------------------------------------------------------------
// 2. TUGMALAR BOSILGANDA (CALLBACK QUERY)
// --------------------------------------------------------------------
if (isset($update->callback_query)) {
    $callback = $update->callback_query;
    $data = $callback->data;
    $ccid = $callback->message->chat->id;
    $cmid = $callback->message->message_id;

    // Bosh menyuga qaytish tugmasi bosilganda
    if ($data == "menyu") {
        bot('deleteMessage', [
            'chat_id' => $ccid,
            'message_id' => $cmid,
        ]);
        bot('sendphoto', [
            'chat_id' => $ccid,
            'photo' => "https://t.me/botim1chi/450",
            'caption' => $start_caption,
            'parse_mode' => 'html',
            'reply_markup' => $regions_keyboard
        ]);
    }

    // Biror viloyat tanlanganda namoz vaqtini ko'rsatish
    if (mb_stripos($data, "time=") !== false) {
        $ex = explode("=", $data);
        $region = $ex[1];

        // Islomapi.uz dan ma'lumot olish
        $api_url = "https://islomapi.uz/api/present/day?region=" . urlencode($region);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $api_response = curl_exec($ch);
        curl_close($ch);

        $api = json_decode($api_response, true);

        if ($api) {
            $qayer = $api['region'];
            $vaqti = $api['date'];
            $hozir = $api['weekday'];
            
            $tong = $api['times']['tong_saharlik'];
            $quyosh = $api['times']['quyosh'];
            $peshin = $api['times']['peshin'];
            $asr = $api['times']['asr'];
            $shom = $api['times']['shom_iftor'];
            $hufton = $api['times']['hufton'];
            
            // Server soati (O'zbekiston vaqti: UTC+5)
            $soat = date("H:i", time() + (5 * 3600)); 

            $text_reply = "<b>🕋 Namoz vaqtlari | $qayer</b>\n\n" .
                          "<b>🌅 Tong otishi</b> - $tong\n" .
                          "<b>🌄 Quyosh chiqishi</b> - $quyosh\n" .
                          "<b>☀️ Peshin vaqti</b> - $peshin\n" .
                          "<b>🌞 Asr vaqti</b> - $asr\n" .
                          "<b>🌜 Shom vaqti</b> - $shom\n" .
                          "<b>🌕 Hufton vaqti</b> - $hufton\n\n" .
                          "<b>$hozir | $vaqti | Soat: $soat</b>";

            bot('deleteMessage', [
                'chat_id' => $ccid,
                'message_id' => $cmid,
            ]);

            bot('sendMessage', [
                'chat_id' => $ccid,
                'text' => $text_reply,
                'parse_mode' => 'html',
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => "🔁 Yangilash", 'callback_data' => "time=$region"]],
                        [['text' => "🏠 Bosh menyu", 'callback_data' => "menyu"]],
                    ]
                ])
            ]);
        } else {
            // Agar Islomapi.uz ishlamay qolsa foydalanuvchiga bildirishnomalar yuborish
            bot('answerCallbackQuery', [
                'callback_query_id' => $callback->id,
                'text' => "⚠️ Ma'lumot olishda xatolik yuz berdi. Qayta urinib ko'ring.",
                'show_alert' => true
            ]);
        }
    }
}
?>
