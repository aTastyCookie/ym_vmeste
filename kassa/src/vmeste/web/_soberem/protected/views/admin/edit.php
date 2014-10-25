<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

?>
<!doctype html>
<html lang="ru">
<head>
      <meta charset="utf-8" />
      <title>Edit</title>
      <link rel="stylesheet" href="/css/font.css" />
      <link rel="stylesheet" href="/css/single_pages.css" />
      <script src="/js/libs/jquery-2.1.1.min.js"></script>
</head>
<body>
<div class="page_wrapper">
    <div class="dialog_box">
        <?php $form=$this->beginWidget('CActiveForm', array(
            'id'=>'login-form',
            'action'=>'/admin/edit/?h='.$h,
            'enableClientValidation'=>true,
            'clientOptions'=>array(
                'validateOnSubmit'=>true,
            ),
        )); ?>
          <div class="dialog_body">
                <h1>Изменить страницу «<?php echo $model->title?>»?</h1>
                <p>Вы сможете изменить страницу в конструкторе, если знаете секретное слово.</p>
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
                      <a class="btn btn-primary" onclick="$('#login-form').submit();" href="#">Открыть конструктор</a>
                      <a class="btn" href="#" onclick="location.href='<?php echo $noUrl; ?>'">Отменить</a>
                </div>
          </div>
        <?php $this->endWidget(); ?>
        </div>
    </div>
</body>
</html>