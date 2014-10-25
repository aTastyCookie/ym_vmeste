<?php

/**
 * Class CollectController
 * Version 2014-10-23-003
 */
class CollectController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
//			// captcha action renders the CAPTCHA image displayed on the contact page
//			'captcha'=>array(
//				'class'=>'CCaptchaAction',
//				'backColor'=>0xFFFFFF,
//			),
//			// page action renders "static" pages stored under 'protected/views/site/pages'
//			// They can be accessed via: index.php?r=site/page&view=FileName
//			'page'=>array(
//				'class'=>'CViewAction',
//			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
        $pageAddress = $this->removeCRLF(Yii::app()->getRequest()->getQuery('page'));
        $model = Pages::model()->findByAttributes(array('page_address'=>$pageAddress),'status=1');
        if($model){
            $ext = pathinfo($model->photo, PATHINFO_EXTENSION);
            $types = array('jpg', 'gif', 'png', 'jpeg');
            if(!in_array($ext, $types)) {
                $model->photo = null;
            }
            $this->render('index',array('model'=>$model));
        }else{
            throw new CHttpException(404,'The specified page cannot be found.');
        }

	}


	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}


    protected function removeCRLF($string) {
        $newstring = '';
        $string = htmlspecialchars($string, ENT_QUOTES);
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string{$i}) != 10 && ord($string{$i}) != 13) {
                $newstring .= $string{$i};
            } else {
                break;
            }
        }
        return $newstring;
    }

//	/**
//	 * Displays the login page
//	 */
//	public function actionLogin()
//	{
//		$model=new LoginForm;
//
//		// if it is ajax validation request
//		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
//		{
//			echo CActiveForm::validate($model);
//			Yii::app()->end();
//		}
//
//		// collect user input data
//		if(isset($_POST['LoginForm']))
//		{
//			$model->attributes=$_POST['LoginForm'];
//			// validate user input and redirect to the previous page if valid
//			if($model->validate() && $model->login())
//				$this->redirect(Yii::app()->user->returnUrl);
//		}
//		// display the login form
//		$this->render('login',array('model'=>$model));
//	}
//
//	/**
//	 * Logs out the current user and redirect to homepage.
//	 */
//	public function actionLogout()
//	{
//		Yii::app()->user->logout();
//		$this->redirect(Yii::app()->homeUrl);
//	}
}