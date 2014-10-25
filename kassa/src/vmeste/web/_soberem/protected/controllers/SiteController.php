<?php

class SiteController extends Controller
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

        $model=new Pages('add');
        $action = Yii::app()->request->getParam('action', 'add');

        // uncomment the following code to enable ajax-based validation
        /*
        if(isset($_POST['ajax']) && $_POST['ajax']==='pages-index-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        */

        if(isset($_POST['Pages']))
        {
            $attributes=$_POST['Pages'];
            $attributes['page_address'] = str_replace('vmeste.yandex.ru/na/','', $attributes['page_address']);
            //$attributes['title'] = htmlspecialchars($attributes['title']);
            //$attributes['text'] = htmlspecialchars($attributes['text']);
            if($action == 'edit'){
                $hash = Yii::app()->request->getParam('h', '');
                $model = Pages::model()->findByAttributes(array(
                    'hash'=>$hash
                ),'status=1');
                session_start();
                if($model && $_SESSION['auth'] == md5($hash . $model->password )){

                }else{
                    throw new CHttpException(404,'The specified page cannot be found.');
                }
            }
            if($action == 'add'){
                $password = $this->_randomPassword();
                $salt = 'yc4ja@#Jls';
                $attributes['hash'] =  md5(time().$salt);
                $attributes['password'] = md5($password.$salt);
            }
            //$model->photo = CUploadedFile::getInstance($model, 'photo');
            $model->photo = $attributes['photo'];
            $model->attributes = $attributes;

            if($model->validate())
            {
                if($model->save())
                {
                    if($model->photo && file_exists( Yii::getPathOfAlias('webroot').'/files/'.$model->photo))
                    {
                        rename(
                            Yii::getPathOfAlias('webroot').'/files/'.$model->photo,
                            Yii::getPathOfAlias('webroot').'/images/uploaded/'.$model->photo
                        );
//                        $fileName = md5(time().'SaltHGYGSYGUKH86565').'.'.$model->photo->getExtensionName();
//                        // $attributes['photo'] = $fileName;
//
//                        $model->photo->saveAs(Yii::getPathOfAlias('webroot').'/images/uploaded/'.$fileName);
//                        $model->photo = $fileName;
//                        $model->save();
                    }
                    if($action == 'add') {
                        //Send email via yiimailer documentation here: http://www.yiiframework.com/extension/yiimailer/

                        $mail = new YiiMailer();
                        $mail->setView('mail');
                        $mail->setData(array(
                            'title' => $attributes['title'],
                            'page_address' => Yii::app()->request->getBaseUrl(true) . '/na/' . $attributes['page_address'],
                            'page_address_edit' => Yii::app()->request->getBaseUrl(true) . '/admin/edit?h=' . $attributes['hash'],
                            'page_address_delete' => Yii::app()->request->getBaseUrl(true) . '/admin/delete?h=' . $attributes['hash'],
                            'password' => $password
                        ));
                        $mail->setFrom('info@money.yandex.ru', 'Yandex Money');
                        $mail->setTo($attributes['email']);
                        $mail->setSubject('Пульт управления вашей страницей');
                        $mail->send();
                    }

                    //$this->redirect('/site/ok');
                    $this->redirect('/na/'.$attributes['page_address']);
                }
            }
        }
        $this->render('index',array(
            'model'=>$model,
            'baseUrl'=>Yii::app()->request->getBaseUrl(true),
            'hash'=>Yii::app()->request->getParam('h', md5(time().rand())),
            'action'=>$action
        ));
	}


    /**
     * This is the 'upload' action
     *
     */
    public function actionUpload()
    {
        require(dirname(__FILE__).'/../vendor/UploadHandler.php');
        $upload_handler = new UploadHandler();
    }

    function _randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * This is the 'ok' action
     */
    public function actionOk()
    {
        $this->render('ok');
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


}