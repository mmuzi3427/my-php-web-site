<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= $title ?? 'Xojiakbar Web' ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <STYle>

:root {
  --tg-bg: #0f172a;
  --tg-card: #111827;
  --tg-blue: #2AABEE;
  --tg-text: #ffffff;
  --tg-muted: #9ca3af;
}
body {
    background-color: var(--tg-theme-secondary-bg-color);
    color: var(--tg-text);
    padding-top: var(--tg-content-safe-area-inset-top);
    padding-left: var(--tg-content-safe-area-inset-left);
    padding-right: var(--tg-content-safe-area-inset-right);
    padding-bottom: var(--tg-content-safe-area-inset-bottom);
    margin: 0;
    box-sizing: border-box;
    user-select: none;
    -moz-user-select:none;
    -ms-user-select:none;
    -webkit-user-select: none;
    -webkit-touch-callout: none; 
}
.top__padding {
    height: 5rem;
    
}
.container {
    padding: 0 0.5rem;
    width: 100%;
    height: 5rem;
    position: fixed;
    bottom: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(rgb(0,0,0,0), rgb(0,0,0,.5));
}

.container__top {
    padding-top:1.9rem;
    width: 100%;
    height: 6rem;
    position: fixed;
    top: 0;
    z-index: 1000;
    display: flex;
    padding-left: calc(0.5rem + var(--tg-viewport-stable-padding, 0px)); 
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    background: linear-gradient(rgba(0,0,0,.7), rgba(0,0,0,0));
}
.brand {
    padding:.3rem;
    margin: 0;
    user-select: none;
    -moz-user-select:none;
    -ms-user-select:none;
    -webkit-user-select: none;
    -webkit-touch-callout: none; 
    display:flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(20, 20, 30, .4);
    backdrop-filter: blur(5px);
    border:1px solid rgb(204, 255, 255);
    border-radius: 1.5rem;
}
.brand__icon {
    margin:0;
    padding-right:.5rem;
}
.brand__icon > div {
    height:1.4rem;
    display:flex;
    justify-content:center;
    
}
.brand__name, .brand__name > p {
    margin:0;
    font-size:.8rem;
    padding-right:.15rem;
    color: var(--tg-link);/*rgb(255,255,255);*/
}
.brand__verify {
    margin:0;
}
.brand__verify > img {
    height:.8rem;
    margin: 0;
    padding: 0;
    margin-left: .3rem;
}

/* Navbar section styles */

.section__navigation {
    height: 5rem;
    box-sizing: border-box;
    /*margin: 0;*/
    padding: 0 .5rem;
    /*width: 100%;*/
    position: fixed;
    bottom: 1.5rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background-color: var(--tg-bg);
    backdrop-filter: blur(5px);
    border-radius: 2.5rem;
}

.navigation__block {
    padding: 0;
    margin: 0;
    display: flex;
    justify-content: space-between;
}

.navigationBlock__item {
    /*width: 2rem;
    height: 2rem;*/
    display: inline-block;
    text-align: center;
    margin: 0;
    padding: .3rem .5rem;
    /*color: --tg-theme-text-color
    */
    text-shadow: .5px .4px rgba(230,230,230,.5);
    transition: .2s;
    border-radius: 2.5rem;
    transform: scale(0.95);
    
}


.navigationBlock__item.active, .navigationBlock__item:hover {
    border-radius: 2.5rem;
    padding: 0.1rem 1rem;
    margin-right: .5rem;
    width: 3rem;
    color: #3b82f6; /* Moviy rang */
    background-color: rgba(30, 41, 59, .5);
    transform: rotate(180deg);
    
    transition: .3s;
    text-shadow: .5px .4px #3b82f6;
    border: .5px solid rgb(255,255,255, 0.5);
}

.oxirgi:hover, .oxirgi.active {
    margin-right: 0;
}

.BlogItem__icon {
    margin: 0;
    padding: 0;
    pointer-events: none;
}

.BlogItem__img {
    display: inline-block;
    font-size: 22px;
    padding: 0;
    margin: 0;
    margin-top: .3rem;
    margin-bottom: .4rem;
}



i {
    display: inline-block;
}

.BlogItem__description, .BlogItem__description > p {
    margin: 0;
    padding: 0;
    font-size: 14px;
    pointer-events: none;
}

.navigationBlock__item:hover > .BlogItem__icon > .BlogItem__img,
.navigationBlock__item.active > .BlogItem__icon > .BlogItem__img {
    transform: rotate(180deg);
}

.navigationBlock__item:hover >.BlogItem__description, 
.navigationBlock__item.active > .BlogItem__description {
    
    transform: rotate(180deg);
}

        </STyle>
        <script src="https://telegram.org/js/telegram-web-app.js"></script>
    </head>
    <body>
        <div class="top__padding"></div>
        <div class="container__top">
            <div onclick="window.location.href='https://t.me/Nematov_Xojiakbar'" class="brand">
                <div class="brand__icon">
                    <div style='background-size: contain; height:1.3rem; width:1.3rem; background-image:url("https://webappinternal.telegram.org/stickers/file?sticker=-G2bHVY_TDP8l1MoKvl_vmTGkcZZ_0qMaDrg06toXs800aEPjecMjT4cwGqFJwqu&thumb=1.png");'></div>
                </div>
                <div class="brand__name">
                    <p>Ne'matov Xojiakbar</p>
                </div>
                <div class="brand__verify">
                    <div style='background-size: contain; height: .7rem; width:.7rem; background-image:url("https://uz.tgstat.com/public/images/verified.png");'></div>
                </div>
            </div>
        </div>
        <div class="container">
            <?php require "includes/navbar.php"?>
        </div>
        