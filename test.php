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
$main_menu = json_encode([
    'inline_keyboard' => [
        [['text' => "👀 Veb Saytimiz", 'web_app' => ['url' => "https://my-php-web-site.onrender.com/site-v1"]]],
        [['text' => "📧 Adminga xabar", 'callback_data' => "message"], ['text' => "✈️ Telegram Kanalimiz", 'url' => "https://t.me/XojiakbarBlogs"]],
        [['text' => "🕌 Namoz vaqtlari", 'callback_data' => "pr_times"]]
    ]
]);
$regions_keyboard = json_encode([
    'inline_keyboard' => [
        [['text' => "🕐 Xiva", 'callback_data' => "time=Xiva"], ['text' => "🕑 Nukus", 'callback_data' => "time=Nukus"]],
        [['text' => "🕒 Qarshi", 'callback_data' => "time=Qarshi"], ['text' => "🕓 Jizzax", 'callback_data' => "time=Jizzax"]],
        [['text' => "🕔 Navoiy", 'callback_data' => "time=Navoiy"], ['text' => "🕕 Buxoro", 'callback_data' => "time=Buxoro"]],
        [['text' => "🕖 Andijon", 'callback_data' => "time=Andijon"], ['text' => "🕝 Guliston", 'callback_data' => "time=Guliston"]],
        [['text' => "🕗 Urganch", 'callback_data' => "time=Urganch"], ['text' => "🕝 Farg'ona", 'callback_data' => "time=Farg'ona"]],
        [['text' => "🕙 Toshkent", 'callback_data' => "time=Toshkent"], ['text' => "🕚 Zarafshon", 'callback_data' => "time=Zarafshon"]],
        [['text' => "🕛 Namangan", 'callback_data' => "time=Namangan"], ['text' => "🕜 Samarqand", 'callback_data' => "time=Samarqand"]],
        [['text' => "🏠 Asosiy menyu", 'callback_data' => "go_main"]]
    ]
]);

$start_caption = "<b>☪ Assalomu aleykum xurmatli foydalanuvchi, botimizga xush kelibsiz!</b>\n\n<i>'Namozni to'kis ado etinglar. Albatta, namoz mo'minlarga vaqtida farz qilingandir'</i>\n<b>Niso surasi, 103-oyat</b>";

// --------------------------------------------------------------------
// 1. CHAT XABARLARINI QABUL QILISH (TEXT)
// --------------------------------------------------------------------
if (isset($update->message)) {
    $message = $update->message;
    $cid = $message->chat->id;
    $tx = $message->text;
    
    $user = $message->from; 
    
    // Ismda maxsus belgilar (<, >, &) bo'lsa, HTML parse_mode buzilmasligi uchun htmlspecialchars ishlatamiz
    $f_name = htmlspecialchars($user->first_name);
    $l_name = htmlspecialchars($user->last_name ?? ''); // Familiya bo'lmasa xato bermasligi uchun
    
    $start_text = "<emoji id='5431407769846575322'>🤍</emoji>Assalomu aleykum hurmatli {$f_name}! Xojiakbar blogs botga xush kelibsiz. \n\nMarhamat oʻzingizga kerakli boʻlimni tanlang! 👇";

    if ($tx == "/start") {
        bot('sendMessage', [
            'chat_id' => $cid,
            'text' => $start_text,
            'parse_mode' => 'html',
            'reply_markup' => $main_menu
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
    $message = $callback->message;
    $user = $message->chat;
    $f_name = $user->first_name;
    $l_name = $user->last_name ?? '';
    $main_menu_text = "Assalomu aleykum hurmatli {$f_name}! Xojiakbar blogs botga xush kelibsiz. \n\nMarhamat oʻzingizga kerakli boʻlimni tanlang! 👇";
    // Bosh menyuga qaytish tugmasi bosilganda
    if ($data == "pr_times") {
        bot('sendphoto', [
            'chat_id' => $ccid,
            'photo' => "https://t.me/botim1chi/450",
            'caption' => $start_caption,
            'parse_mode' => 'html',
            'reply_markup' => $regions_keyboard
        ]);
        bot('deleteMessage', [
            'chat_id' => $ccid,
            'message_id' => $cmid,
        ]);
    }
    if ($data == "go_main") {
        bot('deleteMessage', [
            'chat_id' => $ccid,
            'message_id' => $cmid,
        ]);
        bot('sendMessage', [
            'chat_id' => $ccid,
            'text' => $main_menu_text,
            'parse_mode' => 'html',
            'reply_markup' => $main_menu
        ]);
    }
    if ($data == "menu") {
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
    // trim() orqali ortiqcha bo'shliqlarni olib tashlaymiz
    $region = isset($ex[1]) ? trim($ex[1]) : ''; 

    if (empty($region)) {
        bot('answerCallbackQuery', [
            'callback_query_id' => $callback->id,
            'text' => "⚠️ Shahar nomi aniqlanmadi.",
            'show_alert' => true
        ]);
        exit;
    }

    // Aladhan API url
    $api_url = "https://api.aladhan.com/v1/timingsByCity?city=" . urlencode($region) . "&country=Uzbekistan&&method=2&school=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // SSL tekshiruvini to'liq o'chirish
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // API qotib qolsa bot o'lib qolmasligi uchun
    // Brauzer simulyatsiyasi (bloklanishni oldini oladi)
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'); 
    
    $api_response = curl_exec($ch);
    $curl_error = curl_error($ch); // Agar cURL xato bersa ushlab olamiz
    curl_close($ch);

    $api = json_decode($api_response, true);

    // Xatolikni tekshirishni osonlashtiramiz va status kodini tekshiramiz
    if (mb_stripos($data, "time=") !== false) {
    $ex = explode("=", $data);
    $region = isset($ex[1]) ? trim($ex[1]) : '';

    // Telegramdan keladigan callback_query_id ni aniqlash (buni o'zingizning kodingizga moslang)
    // Agar yuqorida $callback_id aniqlangan bo'lsa, o'shani ishlating
    $cb_id = isset($callback->id) ? $callback->id : (isset($callback_query['id']) ? $callback_query['id'] : null);

    if (empty($region)) {
        if ($cb_id) {
            bot('answerCallbackQuery', [
                'callback_query_id' => $cb_id,
                'text' => "⚠️ Shahar nomi aniqlanmadi.",
                'show_alert' => true
            ]);
        }
        exit;
    }

    // Aladhan API url
    $api_url = "https://api.aladhan.com/v1/timingsByCity?city=" . urlencode($region) . "&country=Uzbekistan&&method=2&school=1";
    
    // cURL muammoli bo'lgani uchun file_get_contents orqali so'rov yuboramiz
    $options = [
        "http" => [
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
            "method" => "GET",
            "timeout" => 10,
            "ignore_errors" => true // API xato bersa ham javobni o'qish uchun
        ],
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ]
    ];
    
    $context = stream_context_create($options);
    $api_response = @file_get_contents($api_url, false, $context);

    // Agar baribir bo'sh qaytsa, muqobil HTTP url ni sinab ko'ramiz (ba'zan SSL muammo bo'ladi)
    if (!$api_response) {
        $api_url_http = "http://api.aladhan.com/v1/timingsByCity?city=" . urlencode($region) . "&country=Uzbekistan&method=3";
        $api_response = @file_get_contents($api_url_http, false, $context);
    }

    $api = json_decode($api_response, true);

    if (isset($api['code']) && $api['code'] == 200) {
        $timings = $api['data']['timings'];
        $date_info = $api['data']['date'];

        // Vaqtlar
        $tong = $timings['Fajr'];
        $quyosh = $timings['Sunrise'];
        $peshin = $timings['Dhuhr'];
        $asr = $timings['Asr'];
        $shom = $timings['Maghrib'];
        $hufton = $timings['Isha'];
        
        // Sana va hafta kuni
        $milodiy_sana = $date_info['gregorian']['date'];
        $hafta_kuni_en = $date_info['gregorian']['weekday']['en'];
        
        $hafta_kunlari = [
            'Monday' => 'Dushanba', 'Tuesday' => 'Seshanba', 'Wednesday' => 'Chorshanba',
            'Thursday' => 'Payshanba', 'Friday' => 'Juma', 'Saturday' => 'Shanba', 'Sunday' => 'Yakshanba'
        ];
        $hozir = $hafta_kunlari[$hafta_kuni_en] ?? $hafta_kuni_en;

        // Server soati
        $soat = date("H:i", time() + (5 * 3600)); 

        $text_reply = "<b>🕋 Namoz vaqtlari | " . ucfirst($region) . "</b>\n\n" .
                      "<b>🌅 Tong otishi</b> - $tong\n" .
                      "<b>🌄 Quyosh chiqishi</b> - $quyosh\n" .
                      "<b>☀️ Peshin vaqti</b> - $peshin\n" .
                      "<b>🌞 Asr vaqti</b> - $asr\n" .
                      "<b>🌜 Shom vaqti</b> - $shom\n" .
                      "<b>🌕 Hufton vaqti</b> - $hufton\n\n" .
                      "<b>$hozir | $milodiy_sana | Soat: $soat</b>";

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
                    [['text' => "🏠 Asosiy menyu", 'callback_data' => "go_main"], ["text" => "🔙 Orqaga qaytish", 'callback_data' => "menu"]],
                ]
            ])
        ]);
    } else {
        // Agar javob baribir bo'sh bo'lsa yoki API xato bersa
        $status = isset($api['code']) ? $api['code'] : 'Server bloklangan (Bo\'sh javob)';
        
        if ($cb_id) {
            bot('answerCallbackQuery', [
                'callback_query_id' => $cb_id,
                'text' => "⚠️ Xatolik yuz berdi. (Status: $status)",
                'show_alert' => true
            ]);
        }
    }
}
    }
    

}
?>
