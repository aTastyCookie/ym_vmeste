<?php
/* @var $this PagesController */
/* @var $model Pages */
/* @var $form CActiveForm */
?>
<!doctype html>
<html lang="ru">
<head>
    <!-- Force latest IE rendering engine or ChromeFrame if installed -->
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Конструктор Скидывайся</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/font.css" />
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/styles.css" />
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/animations.css" data-skrollr-stylesheet />
    <script src="<?php echo $baseUrl; ?>/js/libs/jquery-2.1.1.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/js/libs/jquery.inputmask.js"></script>
    <script src="<?php echo $baseUrl; ?>/js/libs/spin.min.js"></script>
    <script src="<?php echo $baseUrl; ?>/js/app.js"></script>
</head>
<body class="before_load">
<div class="page-wrapper">
<div id="preloader" class="preloader"></div>
<div class="page-holder" data-0="position: fixed;" data-800="position:relative;">
<div class="screen1">
    <div class="scroll_box">
        <section>
            <a class="logo"></a>

            <div class="front_part">
                <h1>Когда всё складывается</h1>
                <p>Собирайте деньги на подарок другу<br>
                    или на доброе дело с помощью красивой<br>
                    персональной страницы в Яндекс.Деньгах.<br>
                </p>
                <a href="#" class="make_page">Создать страницу</a>
                <p>
                    <a href="https://money.yandex.ru/embed/charity/?from=iget" target="_blank" class="make_btn">Сделать кнопку для своего сайта</a>
                </p>
            </div>
            <div class="bike">
                <div class="part1"></div>
                <div class="part2"></div>
                <div class="part3"></div>
                <div class="part4"></div>
                <div class="part5"></div>
                <div class="part6"></div>
                <div class="part7"></div>
            </div>
        </section>
    </div>
</div>
<div class="screen2">
    <section>
        <div class="items">
            <div class="item">
                <i class="pic"></i>
                <p>Добавьте картинку<br>
                    и описание, укажите свой<br>
                    счет в Яндекс.Деньгах</p>
            </div>
            <div class="item">
                <i class="share"></i>
                <p>Поделитесь ссылкой<br>
                    на готовую страницу<br>
                    в интернете</p>
            </div>
            <div class="item">
                <i class="numbers"></i>
                <p>Принимайте переводы<br>
                    Яндекс.Деньгами и с карт<br>
                    Visa и MasterCard</p>
            </div>
        </div>
        <p class="info">
            Комиссия взимается с получателя: 0,5% от суммы при переводе Яндекс.Деньгами, 2% — с банковских карт.
        </p>
        <span class="section_arrow"></span>
    </section>
</div>
<div class="screen3">
    <section>
        <div class="player_holder">
            <div class="left_bottom"></div>
            <div class="right_top"></div>
            <div class="video_box"></div>
        </div>
        <div class="play_circle"></div>
        <div class="play_btn"></div>
        <span class="section_arrow"></span>
    </section>
</div>
<div class="screen4">
    <span class="section_arrow"></span>

        <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'main_form',
            'action' => '/?h='. $hash .'#form_submit',
            'htmlOptions' => array(
                'enctype' => 'multipart/form-data',
                'autocomplete' => 'off'
            ),
            // Please note: When you enable ajax validation, make sure the corresponding
            // controller action is handling ajax validation correctly.
            // See class documentation of CActiveForm for details on this,
            // you need to use the performAjaxValidation()-method described there.
            'enableAjaxValidation'=>false,
        )); ?>
        <?php echo $form->hiddenField($model,'photo', array('id'=>'image_filename')); ?>
        <input type="hidden" name="action" value="<?php echo $action;?>">
        <div class="page_kit">
            <h2>Конструктор страницы</h2>

            <div class="page_form" id="page_form">
                <div class="page_title">
                    <div class="input_field">
                        <?php echo $form->textField($model,'title', array('placeholder'=>'На что вы собираете')); ?>

                        <div class="error_box">
                            <span>Вы забыли придумать заголовок. Например: «На подарок другу».</span>
                        </div>
                    </div>
                </div>
                <div class="page_desc">
                    <div class="input_field">
                        <?php echo $form->textArea($model,'text', array('placeholder'=>'Расскажите в деталях о том, для кого или на что вы собираете деньги')); ?>
                        <div class="error_box">
                            <span>Вы забыли написать, кому и на что вы собираете деньги.</span>
                        </div>
                    </div>
                </div>
                <div class="page_data" id="page_data">
                    <div class="holder">
                        <div class="amount_box">
                            <label>Сумма по умолчанию</label>

                            <div class="input">
                                <?php echo $form->textField($model,'default_amount', array('id' =>'amount')); ?>
                                <span class="rouble">a</span>
                            </div>
                            <div class="payment_icons">
                                <span class="master"> </span>
                                <span class="visa"> </span>
                                <span class="yd"> </span>
                            </div>
                        </div>
                        <div class="image_box" id="image_box">
                            <label for="image_file">
                    <span class="file_input">
                        <?php //echo $form->fileField($model,'photo', array('id'=>'image_file','accept' =>'image/*')); ?>


                            <input id="image_file" type="file" name="files[]" multiple>

                    </span>
                    <span class="add_icon">
                      <span class="tooltip">
                        <span>Можно добавить фото того, на что<br> вы собираете деньги. Подходящее разрешение — от 500 х 300 пикселей.</span>
                      </span>
                    </span>
                            </label>

                            <div class="image_holder">
                                <div class="image" style="margin: 0 0 0"></div>
                                <div class="mask"></div>
                            </div>
                            <div class="remove_icon"></div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div id="page_theme" class="page_theme">
                    <div class="input_field">
                        <label class="theme1"><?php echo $form->radioButton($model,'background', array('value'=>'theme1','uncheckValue'=>NULL)); ?><span></span></label>
                        <label class="theme2"><?php echo $form->radioButton($model,'background', array('value'=>'theme2','uncheckValue'=>NULL)); ?><span></span></label>
                        <label class="theme3"><?php echo $form->radioButton($model,'background', array('value'=>'theme3','uncheckValue'=>NULL)); ?><span></span></label>
                        <label class="theme4"><?php echo $form->radioButton($model,'background', array('value'=>'theme4','uncheckValue'=>NULL)); ?><span></span></label>
                        <label class="theme5"><?php echo $form->radioButton($model,'background', array('value'=>'theme5','uncheckValue'=>NULL)); ?><span></span></label>
                        <label class="theme6"><?php echo $form->radioButton($model,'background', array('value'=>'theme6','uncheckValue'=>NULL)); ?><span></span></label>
                        <div class="tooltip">
                            <span>Пожалуйста, выберите тему</span>
                        </div>
                        <div class="error_box">
                            <span>Пожалуйста, выберите тему</span>
                        </div>


                    </div>
                </div>
            </div>
        </div>
        <section>
            <div class="payment_form">
                <div class="fields">
                    <div class="ym_nubmer item">
                        <label>Номер счета в Яндекс.Деньгах</label>
                        <div class="input_field">
                        <?php echo $form->textField($model,'account'); ?>

                        <div class="error_box">
                            <span>Пожалуйста, введите номер счета в Яндекс.Деньгах.</span>
                        </div>

                    </div>
                    <a href="https://money.yandex.ru/reg" target="_blank">Открыть кошелек</a>
                </div>
                    <div class="page_addr item">
                        <label>Адрес страницы</label>
                        <div class="input_field">
                            <?php echo $form->textField($model,'page_address', array('placeholder'=>'vmeste.yandex.ru/na/', 'id'=>'page_url')); ?>
                            <div class="tooltip">
                                <span>Используйте цифры, латинские буквы, дефисы и подчеркивания — без пробелов.</span>
                            </div>
                            <div class="error_box">
                                <span>Пожалуйста, напишите адрес страницы латинскими буквами без пробелов. Можно использовать цифры, дефисы и подчеркивания.</span>
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <label>Email</label>
                        <div class="input_field">
                            <?php echo $form->textField($model,'email', array('id'=>'email_input')); ?>
                            <div class="tooltip">
                                <span>На этот адрес вы получите ссылку для редактирования страницы</span>
                            </div>
                            <div class="error_box">
                                <span>Пожалуйста, укажите свой email: на него придет ссылка для редактирования страницы.</span>
                                <label class="fio"><i></i><span>ФИО</span></label>
                                <label class="phone"><i></i><span>Телефон</span></label>
                                <label><i></i><span>Email</span></label>
                                <span class="break"></span>
                            </div>
                        </div>
                    </div>
                    <div class="sender_options item">
                        <label>Что должен указать отправитель</label>
                        <div class="bool">
                            <label class="fio"><?php echo $form->checkBox($model,'field_name', array('uncheckValue'=>NULL)); ?><i></i><span>ФИО</span></label>
                            <label class="phone"><?php echo $form->checkBox($model,'field_phone', array('uncheckValue'=>NULL)); ?><i></i><span>Телефон</span></label>
                            <label><?php echo $form->checkBox($model,'field_email', array('uncheckValue'=>NULL)); ?><i></i><span>Email</span></label>
                            <span class="break"></span>
                        </div>
                    </div>
                </div>
                <br>
                <?php echo $form->errorSummary($model); ?>

                <label>
                    <input type="checkbox" value="1" name="field_agree" id="field_agree" /><i></i>
                    <span>Я согласен с <a href="https://money.yandex.ru/doc.xml?id=526810" target="_blank" class="agree">условиями оферты</a></span>
                </label>
                <button class="open_page" type="submit" id="form_submit">Открыть страницу</button>
            </div>
        </section>
    <?php $this->endWidget(); ?>
</div>
<footer>
    <section>
        <p class="phone">8 800 555 80 99</p>
        <a href="mailto:support@money.yandex.ru" class="email">Написать</a>

        <div class="about">
            <p>&copy; 2014, ООО НКО «Яндекс.Деньги»</p>
            <a href="https://money.yandex.ru/about.xml">О компании</a>
        </div>
        <div class="clearfix"></div>
    </section>
</footer>
</div>
</div>
<div id="preload"></div>
<script src="<?php echo $baseUrl; ?>/js/libs/skrollr.stylesheets.min.js"></script>
<script src="<?php echo $baseUrl; ?>/js/libs/skrollr.min.js"></script>
<!--<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>-->
<!-- The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
<script src="<?php echo $baseUrl; ?>/js/vendor/jquery.ui.widget.js"></script>
<!-- The Iframe Transport is required for browsers without support for XHR file uploads -->
<script src="<?php echo $baseUrl; ?>/js/jquery.iframe-transport.js"></script>
<!-- The basic File Upload plugin -->
<script src="<?php echo $baseUrl; ?>/js/jquery.fileupload.js"></script>

<script>
    /*jslint unparam: true */
    /*global window, $ */
    $(function () {
        'use strict';
        // Change this to the location of your server-side upload handler:
        var url = 'site/upload/';
        $('#image_file').fileupload({
            url: url,
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    $('.image').html();
                    $('.image').html('<img src="files/'+file.name+'">');
                    $('#image_filename').val(file.name);
                });
            }
        }).prop('disabled', !$.support.fileInput)
            .parent().addClass($.support.fileInput ? undefined : 'disabled');
    });
</script>
<div style="margin:30px">
    <h1 style="margin:30px">Что-то не так</h1>
    <p>Возникли технические неполадки.</p>
    <p>Мы знаем о проблеме и решаем ее.</p>
</div>
</body>
</html>



































