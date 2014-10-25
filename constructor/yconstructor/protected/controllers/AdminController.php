<?php

/**
 * Class AdminController
 * Version 2014-10-24-006
 */
class AdminController extends Controller
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
        $http = Yii::app()->request->getIsSecureConnection() ? 'https' : 'http';
        if($http == 'http') {
            $port = $_SERVER['SERVER_PORT'];
            if($port!=80) {
                $http = 'https';
            }
        }
        $base = $http . '://'.$_SERVER['SERVER_NAME'] . rtrim(dirname(Yii::app()->request->getScriptUrl()),'\\/');
        $this->redirect($base);
	}
    /**
     * This is the  'edit' action.
     */
    public function actionEdit()
    {
        $http = Yii::app()->request->getIsSecureConnection() ? 'https' : 'http';
        $base = $http . '://'.$_SERVER['SERVER_NAME'] . rtrim(dirname(Yii::app()->request->getScriptUrl()),'\\/');

        if(Yii::app()->request->getParam('LoginForm', 0)){
            $attributes = Yii::app()->request->getParam('LoginForm', 0);
            $password = $this->removeCRLF(!empty($attributes['password'])?$attributes['password']:'');
            $hashedPassword = md5($password . 'yc4ja@#Jls');
            $hash = $this->removeCRLF(Yii::app()->request->getParam('h', ''));
            $model = Pages::model()->findByAttributes(array(
                'hash'=>$hash,
                'password'=>$hashedPassword
            ),'status=1');
            if($model){
                session_start();
                $_SESSION['auth'] = md5($hash.$hashedPassword);
                $this->render('../site/index',array(
                    'model'=>$model,
                    'baseUrl'=>$base,
                    'hash'=>$this->removeCRLF(Yii::app()->request->getParam('h', md5(time().rand()))),
                    'action'=>'edit'

                ));
                exit();
            }else{
                throw new CHttpException(404,'The specified page cannot be found.');
            }
        }
        $hash = $this->removeCRLF(Yii::app()->request->getParam('h', ''));
        $model = Pages::model()->findByAttributes(array(
            'hash'=>$hash
        ),'status=1');
        if($model){
            $noUrl = '/admin/noaction?h='.$hash;
            $this->render('edit',array(
                'model'=>$model,
                'noUrl'=>$noUrl,
                'h'=>$this->removeCRLF(Yii::app()->request->getParam('h', md5(rand().time().'SaLt12232382'))),
                'loginForm'=> new LoginForm
            ));
        }else{
            throw new CHttpException(404,'The specified page cannot be found.');
        }

    }

    /**
     * This is the 'delete' action.
     */
    public function actionDelete()
    {
        if(Yii::app()->request->getParam('LoginForm', 0)){
            $attributes = Yii::app()->request->getParam('LoginForm', 0);
            $password = $this->removeCRLF(!empty($attributes['password'])?$attributes['password']:'');
            $hashedPassword = md5($password . 'yc4ja@#Jls');
            $hash = $this->removeCRLF(Yii::app()->request->getParam('h', ''));
            $model = Pages::model()->findByAttributes(array(
                'hash'=>$hash,
                 'password'=>$hashedPassword
            ),'status=1');
            if($model){
                session_start();
                $_SESSION['auth'] = md5($hash.$hashedPassword);
                $this->redirect('/admin/deleted?h='.$this->removeCRLF(Yii::app()->request->getParam('h', md5(rand().time().'SaLt12232382'))));
            }else{
                throw new CHttpException(404,'The specified page cannot be found.');
            }
        }
        $hash = $this->removeCRLF(Yii::app()->request->getParam('h', ''));
        $model = Pages::model()->findByAttributes(array(
            'hash'=>$hash,
        ),'status=1');

        if($model){
            $noUrl = '/admin/noaction?h='.$hash;
            $this->render('delete',array(
                'model'=>$model,
                'noUrl'=>$noUrl,
                'h'=>$this->removeCRLF(Yii::app()->request->getParam('h', md5(rand().time().'SaLt12232382'))),
                'loginForm'=> new LoginForm
            ));
        }else{
            throw new CHttpException(404,'The specified page cannot be found.');
        }

    }

    /**
     * This is the 'delete' action.
     */
    public function actionDeleted()
    {
        $hash = $this->removeCRLF(Yii::app()->request->getParam('h', ''));
        $model = Pages::model()->findByAttributes(array(
            'hash'=>$hash
        ),'status=1');
        session_start();
        if($model && $_SESSION['auth'] == md5($hash . $model->password )){
            $model->status = 0;
            $model->save();
            $this->render('deleted',array(
                'model'=>$model
            ));
        }else{
            throw new CHttpException(404,'The specified page cannot be found.');
        }

    }

    public function actionNoaction()
    {
        $hash = $this->removeCRLF(Yii::app()->request->getParam('h', ''));
        $model = Pages::model()->findByAttributes(array(
            'hash'=>$hash
        ),'status=1');
        if($model){
            $this->render('noaction',array(
                'model'=>$model
            ));
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


	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;
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
		// display the login form
		$this->render('login',array(
            'model'=>$model,
            'h'=>$this->removeCRLF(Yii::app()->request->getParam('h', md5(rand().time().'SaLt12232382'))),
            'action'=>Yii::app()->request->getParam('action', ''),
        ));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
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
}