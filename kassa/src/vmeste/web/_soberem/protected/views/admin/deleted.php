<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <title>Deleted</title>
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/single_pages.css" />
    <script src="/js/libs/jquery-2.1.1.min.js"></script>
</head>
<body>
<div class="page_wrapper">
    <div class="dialog_box">
        <div class="dialog_body">
            <i class="dialog_icon icon_sad"></i>
            <h1>Вы удалили страницу «<?php echo $model->title?>».</h1>
            <p>Новую страницу можно создать в любой момент.</p>
        </div>
        <div class="dialog_footer">
            <div class="actions">
                <a class="btn" href="/">Создать страницу</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
