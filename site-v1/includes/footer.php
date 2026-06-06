        <script>
            const tg = window.Telegram.WebApp;
            tg.ready();
            //tg.MainButton.type="main button";
            tg.MainButton.iconCustomEmojiId="5431407769846575322";
            tg.MainButton.text="Telegram kanalga oʻtish";
            tg.MainButton.show();
            tg.MainButton.hasShineEffect = true;
            tg.MainButton.onClick(function() {
                tg.openTelegramLink("https://t.me/XojiakbarBlogs");
                //tg.showAlert("Siz pastki native tugmani bostingiz!");
    
                // Kerak bo'lsa amallardan keyin tugmani yashirish mumkin:
                //tg.MainButton.hide();
            });
            //tg.MainButton.onClick(tg.openTelegramLink("https://t.me/XojiakbarBlogs"));
            
            if (!tg.isFullscreen){
                tg.requestFullscreen();
            }
            function applyTheme() {
                const t = tg.themeParams; 

                if (t) {
                    document.documentElement.style.setProperty('--tg-bg', t.bg_color || '#ffffff');
                    document.documentElement.style.setProperty('--tg-text', t.text_color || '#000000');
                    document.documentElement.style.setProperty('--tg-hint', t.hint_color || '#707579');
                    document.documentElement.style.setProperty('--tg-link', t.link_color || '#2481cc');
        
                    // Ba'zi eski versiyalarda card_color bo'lmasligi mumkin, zaxira rang beramiz
                    document.documentElement.style.setProperty('--tg-card', t.card_color || t.secondary_bg_color || '#f4f5f7');
            
                    document.documentElement.style.setProperty('--tg-button', t.button_color || '#2481cc');
                    document.documentElement.style.setProperty('--tg-button-text', t.button_text_color || '#ffffff');
                    document.documentElement.style.setProperty('--tg-secondary-bg', t.secondary_bg_color || '#111827');
                    //tg.showAlert(`Dastur ishga tushdi va ranglar yangilandi\n tugma rangi: ${t.button_color}da`);
                    
                }
            }
            // Ranglarni birinchi marta yuklash
            applyTheme();
            // Telegramda mavzu o'zgarganda ishga tushirish
            tg.onEvent('themeChanged', applyTheme);
        // Telefon og'ishini kuzatish
window.addEventListener('deviceorientation', handleOrientation);

function handleOrientation(event) {
    // gamma: telefonning chapga yoki o'ngga og'ish burchagi (darajada: -90 dan 90 gacha)
    let burchak = event.gamma; 

    // Agar ma'lumot kelayotgan bo'lsa
    if (burchak !== null) {
        
        // Keskin tebranishlarni yumshatish uchun burchakni biroz cheklaymiz
        // Masalan, telefon 30 daraja o'ngga og'sa, elementni -30 daraja chapga buramiz
        let teskariBurchak = -burchak;

        // Saytdagi doimo to'g'ri turishi kerak bo'lgan elementni topamiz
        // Masalan, siz yaratgan pastki boshqaruv paneli yoki profil bloki
        const muvozanatElementi = document.querySelector('.navbar'); // yoki '.brand'
        
        if (muvozanatElementi) {
            // Elementni teskari burchakka burib, muvozanatni saqlaymiz
            muvozanatElementi.style.transform = `rotate(${teskariBurchak}deg)`;
            
            // Burilish vizual ravishda silliq (smooth) bo'lishi uchun CSS transition bering
            muvozanatElementi.style.transition = "transform 0.1s ease-out";
        }
    }
}
</script>

        <!--<script src="main.js" type="text/javascript" charset="utf-8"></script>
        -->
    </body>
</html>