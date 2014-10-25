<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

?>


<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'login-form',
	'action'=>'/admin/'.$action.'?h='.$h,
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>




		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password'); ?>
		<?php echo $form->error($model,'password'); ?>




		<?php echo CHtml::submitButton('Login'); ?>


<?php $this->endWidget(); ?>
</div><!-- form -->
