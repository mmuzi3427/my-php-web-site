<?php
define('API_KEY', getenv("TOKEN"));

$admin = getenv("admin_ID");

function bot($method,$datas=[]){
$url = "https://api.telegram.org/bot".API_KEY."/".$method;
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
$res = curl_exec($ch);
if(curl_error($ch)){
var_dump(curl_error($ch));
}else{
return json_decode($res);
}}

function joinchat($id){
global $mid;
$array = array("inline_keyboard");
$kanallar=file_get_contents("channel.txt");
if($kanallar == null){
return true;
}else{
$ex = explode("\n",$kanallar);
for($i=0;$i<=count($ex) -1;$i++){
$first_line = $ex[$i];
$first_ex = explode("@",$first_line);
$url = $first_ex[1];
$ism=bot('getChat',['chat_id'=>"@".$url,])->result->title;
$ret = bot("getChatMember",[
"chat_id"=>"@$url",
"user_id"=>$id,
]);
$stat = $ret->result->status;
if((($stat=="creator" or $stat=="administrator" or $stat=="member"))){
$array['inline_keyboard']["$i"][0]['text'] = "✅ ". $ism;
$array['inline_keyboard']["$i"][0]['url'] = "https://t.me/$url";
}else{
$array['inline_keyboard']["$i"][0]['text'] = "❌ ". $ism;
$array['inline_keyboard']["$i"][0]['url'] = "https://t.me/$url";
$uns = true;
}
}
$array['inline_keyboard']["$i"][0]['text'] = "🔄 Tekshirish";
$array['inline_keyboard']["$i"][0]['callback_data'] = "azo_boldim";
if($uns == true){
bot('sendMessage',[
'chat_id'=>$id,
'text'=>"<b>⚠️ Botdan to'liq foydalanish uchun quyidagi kanallarimizga obuna bo'ling!</b>",
'parse_mode'=>'html',
'disable_web_page_preview'=>true,
'reply_markup'=>json_encode($array),
]);
return false;
}else{
return true;
}}}

$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$cid = $message->chat->id;
$tx = $message->text;
$mid = $message->message_id;
$name = $message->from->first_name;
$fid = $message->from->id;
$callback = $update->callback_query;
$data = $callback->data;
$callid = $callback->id;
$ccid = $callback->message->chat->id;
$cmid = $callback->message->message_id;
$from_id = $update->message->from->id;
$token = $message->text;
$text = $message->text;
$name = $message->from->first_name;
$message_id = $callback->message->message_id;
$data = $update->callback_query->data;
$callcid=$update->callback_query->message->chat->id;
$cqid = $update->callback_query->id;
$callfrid = $update->callback_query->from->id;
$botname = bot('getme',['bot'])->result->username;
#-----------------------------
mkdir("step");
#-----------------------------

if(!file_exists("channel.txt")){
file_put_contents("channel.txt","");
}
if(file_get_contents("obunachi.txt")){
} else{
file_put_contents("obunachi.txt", "");
}

$statistika=file_get_contents("obunachi.txt");
$soat=date("H:i",strtotime("2 hour"));
$userstep=file_get_contents("step/$fid.txt");
$kanallar=file_get_contents("channel.txt");

if(isset($callback)){
$get = file_get_contents("obunachi.txt");
if(mb_stripos($get,$callfrid)==false){
file_put_contents("obunachi.txt", "$get\n$callfrid");
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>👤 Yangi obunachi qo'shildi</b>",
'parse_mode'=>"html"
]);
}}

if(isset($message)){
$get = file_get_contents("obunachi.txt");
if(mb_stripos($get,$fid)==false){
file_put_contents("obunachi.txt",  "$get\n$fid");
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>👤 Yangi obunachi qo'shildi</b>",
'parse_mode'=>"html"
]);
}}

if($tx=="/start" and joinchat($cid)=="true"){
bot('sendphoto',[
'chat_id'=>$cid,
'photo'=>"https://t.me/botim1chi/450",
'caption'=>"<b>☪ Assalomu alaykum xurmatli foydalanuvchi botimizga xush kelibsiz:</b>

<i>'Namozni to'kis ado etinglar. Albatta, namoz mo'minlarga vaqtida farz qilingandir'</i>
<b>Niso surasi, 103-oyat</b>",
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🕐 Xiva",'callback_data'=>"time=Xiva"],['text'=>"🕑 Nukus",'callback_data'=>"time=Nukus"]],
[['text'=>"🕒 Qarshi",'callback_data'=>"time=Qarshi"],['text'=>"🕓 Jizzax",'callback_data'=>"time=Jizzax"]],
[['text'=>"🕔 Navoiy",'callback_data'=>"time=Navoiy"],['text'=>"🕕 Buxoro",'callback_data'=>"time=Buxoro"]],
[['text'=>"🕖 Andijon",'callback_data'=>"time=Andijon"],['text'=>"🕝 Guliston",'callback_data'=>"time=Guliston"]],
[['text'=>"🕗 Urganch",'callback_data'=>"time=Urganch"],['text'=>"🕘 Farg'ona",'callback_data'=>"time=Farg'ona"]],
[['text'=>"🕙 Toshkent",'callback_data'=>"time=Toshkent"],['text'=>"🕚 Zarafshon",'callback_data'=>"time=Zarafshon"]],
[['text'=>"🕛 Namangan",'callback_data'=>"time=Namangan"],['text'=>"🕜 Samarqand",'callback_data'=>"time=Samarqand"]],
]])
]);
unlink("step/$cid.txt");
}

if($data == "azo_boldim"){
if(joinchat($ccid) == "true"){
bot('deleteMessage',[
'chat_id'=>$ccid,
'message_id'=>$cmid,
]);
bot('sendphoto',[
'chat_id'=>$ccid,
'photo'=>"https://t.me/botim1chi/450",
'caption'=>"<b>☪ Assalomu alaykum xurmatli foydalanuvchi botimizga xush kelibsiz:</b>


<i>'Namozni to'kis ado etinglar. Albatta, namoz mo'minlarga vaqtida farz qilingandir'</i>
<b>Niso surasi, 103-oyat</b>",
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🕐 Xiva",'callback_data'=>"time=Xiva"],['text'=>"🕑 Nukus",'callback_data'=>"time=Nukus"]],
[['text'=>"🕒 Qarshi",'callback_data'=>"time=Qarshi"],['text'=>"🕓 Jizzax",'callback_data'=>"time=Jizzax"]],
[['text'=>"🕔 Navoiy",'callback_data'=>"time=Navoiy"],['text'=>"🕕 Buxoro",'callback_data'=>"time=Buxoro"]],
[['text'=>"🕖 Andijon",'callback_data'=>"time=Andijon"],['text'=>"🕝 Guliston",'callback_data'=>"time=Guliston"]],
[['text'=>"🕗 Urganch",'callback_data'=>"time=Urganch"],['text'=>"🕘 Farg'ona",'callback_data'=>"time=Farg'ona"]],
[['text'=>"🕙 Toshkent",'callback_data'=>"time=Toshkent"],['text'=>"🕚 Zarafshon",'callback_data'=>"time=Zarafshon"]],
[['text'=>"🕛 Namangan",'callback_data'=>"time=Namangan"],['text'=>"🕜 Samarqand",'callback_data'=>"time=Samarqand"]],
]])
]);
}else{
bot('deleteMessage',[
'chat_id'=>$ccid,
'message_id'=>$cmid,
]);
}}

if($data == "menyu" and joinchat($ccid) == "true"){
bot('deleteMessage',[
'chat_id'=>$ccid,
'message_id'=>$cmid,
]);
bot('sendphoto',[
'chat_id'=>$ccid,
'photo'=>"https://t.me/botim1chi/450",
'caption'=>"<b>☪ Assalomu alaykum xurmatli foydalanuvchi botimizga xush kelibsiz:</b>

<i>'Namozni to'kis ado etinglar. Albatta, namoz mo'minlarga vaqtida farz qilingandir'</i>
<b>Niso surasi, 103-oyat</b>",
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🕐 Xiva",'callback_data'=>"time=Xiva"],['text'=>"🕑 Nukus",'callback_data'=>"time=Nukus"]],
[['text'=>"🕒 Qarshi",'callback_data'=>"time=Qarshi"],['text'=>"🕓 Jizzax",'callback_data'=>"time=Jizzax"]],
[['text'=>"🕔 Navoiy",'callback_data'=>"time=Navoiy"],['text'=>"🕕 Buxoro",'callback_data'=>"time=Buxoro"]],
[['text'=>"🕖 Andijon",'callback_data'=>"time=Andijon"],['text'=>"🕝 Guliston",'callback_data'=>"time=Guliston"]],
[['text'=>"🕗 Urganch",'callback_data'=>"time=Urganch"],['text'=>"🕘 Farg'ona",'callback_data'=>"time=Farg'ona"]],
[['text'=>"🕙 Toshkent",'callback_data'=>"time=Toshkent"],['text'=>"🕚 Zarafshon",'callback_data'=>"time=Zarafshon"]],
[['text'=>"🕛 Namangan",'callback_data'=>"time=Namangan"],['text'=>"🕜 Samarqand",'callback_data'=>"time=Samarqand"]],
]])
]);
unlink("step/$ccid.txt");
}

if(mb_stripos($data, "time=")!==false){
$ex = explode("=",$data);
$region = $ex[1];
$api = json_decode(file_get_contents("https://islomapi.uz/api/present/day?region=$region"),true);
$qayer = $api['region'];
$vaqti = $api['date'];
$hozir = $api['weekday'];
$tong = $api['times']['tong_saharlik'];
$quyosh = $api['times']['quyosh'];
$peshin = $api['times']['peshin'];
$asr = $api['times']['asr'];
$shom = $api['times']['shom_iftor'];
$hufton = $api['times']['hufton'];
bot('deleteMessage',[
'chat_id'=>$ccid,
'message_id'=>$cmid,
]);
bot('sendMessage',[
'chat_id'=>$ccid,
'text'=>"<b>🕋 Namoz vaqtlari | $qayer</b>

<b>🌅 Tong otishi</b> - $tong
<b>🌄 Quyosh chiqishi</b> - $quyosh
<b>☀️ Peshin vaqti</b> - $peshin
<b>🌞 Asr vaqti</b> - $asr 
<b>🌜 Shom vaqti</b> - $shom
<b>🌕 Hufton vaqti</b> - $hufton

<b>$hozir | $vaqti | $soat</b>",
'parse_mode'=>'html',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"Yangilash",'callback_data'=>"time=$region"]],
[['text'=>"🏠 Bosh menyu",'callback_data'=>"menyu"]],
]])
]);
}

$admin1_menu = json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"📨 Xabar yuborish"]],
[['text'=>"📢 Kanallar"],['text'=>"📊 Statistika"]],
]]);

if($tx == "🗄 Boshqaruv" and $cid == $admin){
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>🗄 Boshqaruv paneliga xush kelibsiz!</b>",
'parse_mode'=>"html",
'reply_markup'=>$admin1_menu
]);
unlink("step/$cid.txt");
}

$oddiy_xabar = file_get_contents("oddiy.txt");
if($data == "oddiy_xabar" and $ccid==$admin){
$lich=substr_count($statistika,"\n");
bot('deleteMessage',[
'chat_id'=>$admin,
'message_id'=>$cmid,
]);
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>$lich ta foydalanuvchiga yuboriladigan xabar matnini yuboring:</b>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"🗄 Boshqaruv"]],
]])
]);
file_put_contents("oddiy.txt","oddiy");
}
if($oddiy_xabar=="oddiy" and $cid==$admin){
if($tx=="🗄 Boshqaruv"){
unlink("oddiy.txt");
}else{
$lich=substr_count($statistika,"\n");
bot('sendmessage',[
'chat_id'=>$admin,
'text'=>"<b>$lich ta foydalanuvchiga xabar yuborish boshlandi!</b>",
'parse_mode'=>"html",
'reply_markup'=>$admin1_menu,
]);
$lichka = explode("\n",$statistika);
foreach($lichka as $lichkalar){
$usr=bot("sendMessage",[
'chat_id'=>$lichkalar,
'text'=>$text,
'parse_mode'=>'HTML'
]);
unlink("oddiy.txt");
}}}
if($usr){
$lich=substr_count($statistika,"\n");
bot("sendmessage",[
'chat_id'=>$admin,
'text'=>"<b>$lich ta foydalanuvchiga muvaffaqiyatli yuborildi</b>",
'parse_mode'=>'html',
'reply_markup'=>$admin1_menu,
]);
unlink("oddiy.txt");
}

$forward_xabar = file_get_contents("forward.txt");
if($data =="forward_xabar" and $ccid==$admin){
$lich=substr_count($statistika,"\n");
bot('deleteMessage',[
'chat_id'=>$admin,
'message_id'=>$cmid,
]);
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>$lich ta foydalanuvchiga yuboriladigan xabarni forward shaklida yuboring:</b>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"🗄 Boshqaruv"]],
]])
]);
file_put_contents("forward.txt","forward");
}
if($forward_xabar=="forward" and $cid==$admin){
if($tx=="🗄 Boshqaruv"){
unlink("forward.txt");
}else{
$lich=substr_count($statistika,"\n");
bot('sendmessage',[
'chat_id'=>$admin,
'text'=>"<b>$lich ta foydalanuvchiga xabar yuborish boshlandi!</b>",
'parse_mode'=>"html",
'reply_markup'=>$admin1_menu,
]);
$lichka = explode("\n",$statistika);
foreach($lichka as $lichkalar){
$fors=bot("forwardMessage",[
'from_chat_id'=>$cid,
'chat_id'=>$lichkalar,
'message_id'=>$mid,
]);
unlink("forward.txt");
}}}
if($fors){
$lich=substr_count($statistika,"\n");
bot("sendmessage",[
'chat_id'=>$admin,
'text'=>"<b>$lich ta foydalanuvchiga muvaffaqiyatli yuborildi</b>",
'parse_mode'=>'html',
'reply_markup'=>$admin1_menu,
]);
unlink("forward.txt");
}

if($tx=="📨 Xabar yuborish" and $cid==$admin){
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>📨 Yuboriladigan xabar turini tanlang:</b>",
'parse_mode'=>"html",
'reply_markup'=> json_encode([
'inline_keyboard'=>[
[['text'=>"Oddiy xabar",'callback_data'=>"oddiy_xabar"]],
[['text'=>"Forward xabar",'callback_data'=>"forward_xabar"]],
]])
]);
}

$admin6_menu = json_encode([
'inline_keyboard'=>[
[['text'=>"🔐 Majburiy obuna",'callback_data'=>"majburiy_obuna"]],
]]);

if($data=="kanalsoz" and $ccid==$admin){
bot('deleteMessage',[
'chat_id'=>$admin,
'message_id'=>$cmid,
]);
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>Quyidagilardan birini tanlang:</b>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🔐 Majburiy obuna",'callback_data'=>"majburiy_obuna"]],
]])
]);
unlink("step/$ccid.txt");
}

if($tx == "📊 Statistika" and $cid == $admin){
$lich=substr_count($statistika,"\n");
$load = sys_getloadavg();
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>💡 O'rtacha yuklanish:</b> <code>$load[0]</code>

👥 <b>Foydalanuvchilar: $lich ta</b>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🔁 Yangilash",'callback_data'=>"stats"]],
]])
]);
}

if($data=="stats" and $ccid == $admin){
$lich=substr_count($statistika,"\n");
$load = sys_getloadavg();
bot('deleteMessage',[
'chat_id'=>$admin,
'message_id'=>$cmid,
]);
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>💡 O'rtacha yuklanish:</b> <code>$load[0]</code>

👥 <b>Foydalanuvchilar: $lich ta</b>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"🔁 Yangilash",'callback_data'=>"stats"]],
]])
]);
}

if($tx=="📢 Kanallar" and $cid==$admin){
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>Quyidagilardan birini tanlang:</b>",
'parse_mode'=>"html",
'reply_markup'=>$admin6_menu
]);
}

if($data=="majburiy_obuna" and $ccid==$admin){
bot('editMessageText',[
'chat_id'=>$admin,
'message_id'=>$cmid,
'text'=>"<b>Majburiy obunalarni sozlash bo'limidasiz:</b>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"📋 Ro'yxatni ko'rish",'callback_data'=>"majburiy_obuna3"]],
[['text'=>"➕ Kanal qo'shish",'callback_data'=>"majburiy_obuna1"],['text'=>"🗑 O'chirish",'callback_data'=>"majburiy_obuna2"]],
[['text'=>"◀️ Orqaga",'callback_data'=>"kanalsoz"]],

]])
]);
unlink("step/$cid.txt");
}

$majburiy = file_get_contents("maj.txt");
if($data=="majburiy_obuna1" and $ccid == $admin){
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>📢 Kerakli kanalni manzilini yuboring:</b>

Namuna: @HaydarovUz",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'resize_keyboard'=>true,
'keyboard'=>[
[['text'=>"🗄 Boshqaruv"]],
]])
]);
file_put_contents("maj.txt","majburiy1");
}
if($majburiy == "majburiy1" and $cid==$admin){
if($tx=="🗄 Boshqaruv"){
unlink("maj.txt");
}else{
if(stripos($text,"@")!==false){
if($kanallar == null){
file_put_contents("channel.txt",$text);
bot('SendMessage',[
'chat_id'=>$admin,
'text'=>"<b>$text - kanal qo'shildi</b>",
'parse_mode'=>'html',
'reply_markup'=>$admin1_menu,
]);
unlink("maj.txt");
}else{
file_put_contents("channel.txt","$kanallar\n$text");
bot('SendMessage',[
'chat_id'=>$admin,
'text'=>"<b>$text - kanal qo'shildi</b>",
'parse_mode'=>'html',
'reply_markup'=>$admin1_menu,
]);
unlink("maj.txt");
}}else{
bot('SendMessage',[
'chat_id'=>$cid,
'text'=>"<b>⚠️ Kanal manzili kiritishda xatolik:</b>

Masalan: @HaydarovUz",
'parse_mode'=>'html',
]);
}}}

if($data=="majburiy_obuna2" and $ccid == $admin){
bot('deleteMessage',[
'chat_id'=>$admin,
'message_id'=>$cmid,
]);
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"<b>🗑 Kanallar o'chirildi!</b>",
'parse_mode'=>"html",
]);
unlink("channel.txt");
}

if($data=="majburiy_obuna3" and $ccid==$admin){
if($kanallar==null){
bot('editMessageText',[
'chat_id'=>$admin,
'message_id'=>$cmid,
'text'=>"<b>Kanallar ulanmagan!</b>",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"◀️ Orqaga",'callback_data'=>"majburiy_obuna"]],
]])
]);
}else{
$soni = substr_count($kanallar,"@");
bot('editMessageText',[
'chat_id'=>$admin,
'message_id'=>$cmid,
'text'=>"<b>Ulangan kanallar ro'yxati ⤵️</b>
➖➖➖➖➖➖➖➖

<i>$kanallar</i>

<b>Ulangan kanallar soni:</b> $soni ta",
'parse_mode'=>"html",
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"◀️ Orqaga",'callback_data'=>"majburiy_obuna"]],
]])
]);
}}

if($tx=="/panel" and $cid==$admin){
bot('sendMessage',[
'chat_id'=>$admin,
'text'=>"🖥",
'reply_markup'=>$admin1_menu,
]);
unlink("admin/$cid.txt");
}
?>
