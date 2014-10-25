<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

?>
<!doctype html>
<html  lang="ru">
<head>
    <meta charset="utf-8" />
    <title>Delete</title>
    <link rel="stylesheet" href="/css/font.css" />
    <link rel="stylesheet" href="/css/single_pages.css" />
    <script src="/js/libs/jquery-2.1.1.min.js"></script>
</head>
<body>
<div class="page_wrapper">
    <div class="dialog_box">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'login-form',
            'action'=>'/admin/delete/?h='.$h,
            'enableClientValidation'=>true,
            'clientOptions'=>array(
                'validateOnSubmit'=>true,
            ),
        )); ?>
        <div class="dialog_body">
            <h1>Удалить страницу «<?php echo $model->title?>»?</h1>
            <p>Вы не сможете собирать деньги, а кто-то другой сможет занять этот адрес:<br>
                <a  href="http:/vmeste.yandex.ru/soberem/<?php echo $model->page_address?>">vmeste.yandex.ru/soberem/<?php echo $model->page_address?></a></p>

            <div class="secret_word">
                <label>Секретное слово</label>

                <div class="input">
                    <?php echo $form->passwordField($loginForm,'password'); ?>
                    <?php echo $form->error($loginForm,'password'); ?>
                </div>
            </div>
        </div>
        <div class="dialog_footer">
            <div class="actions">
                <a class="btn btn-primary" onclick="$('#login-form').submit();" href="#">Удалить</a>
                <a class="btn" href="#" onclick="location.href='<?php echo $noUrl; ?>'">Отменить</a>
            </div>
        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
</body>
</html>

