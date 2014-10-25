<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <title>Cancel</title>
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/single_pages.css" />
    <script src="/js/libs/jquery-2.1.1.min.js"></script>
</head>
<body>
<div class="page_wrapper">
    <div class="dialog_box">
        <div class="dialog_body">
            <i class="dialog_icon icon_smile"></i>

            <h1>Все в порядке</h1>
            <p>Сбор денег продолжается на странице «<?php echo $model->title?>».</p>
        </div>
        <div class="dialog_footer">
            <div class="infos_box">
                <h2>Что дальше</h2>

                <div class="items">
                    <div class="item">
                        <a target="_blank" href="https://money.yandex.ru/">Проверить баланс кошелька</a>
                        <span>чтобы узнать, сколько вы уже собрали.</span>
                    </div>
                    <div class="item">
                        <a target="_blank" href="https://money.yandex.ru/ymc/promo.xml">Выпустить карту Яндекс.Денег</a>
                        <span>для получения наличных в банкомате.</span>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

