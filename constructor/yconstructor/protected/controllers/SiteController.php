<?php

/**
 * Class SiteController
 * Version 2014-10-27-006
 */

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

        $http = Yii::app()->request->getIsSecureConnection() ? 'https' : 'http';
        if($http == 'http') {
            $port = $_SERVER['SERVER_PORT'];
            if($port!=80) {
                $http = 'https';
            }
        }
        $base = $http . '://'.$_SERVER['SERVER_NAME'] . rtrim(dirname(Yii::app()->request->getScriptUrl()),'\\/');

        $model = new Pages('add');
        $action = $this->removeCRLF(Yii::app()->request->getParam('action', 'add'));


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
            $checkAttempts = Attempts::check();
            if($checkAttempts) {
                $model->addError('photo', 'С Вашего IP-адреса недавно уже была создана страница. Попробуйте снова немного позже.');
            } else {
                Attempts::add();
            }

            $attributes = $_POST['Pages'];
            $attributes['page_address'] = $this->removeCRLF(str_replace('vmeste.yandex.ru/na/','', $attributes['page_address']));
            $attributes['title'] = $this->removeCRLF($attributes['title']);
            $attributes['text'] = $this->removeCRLF($attributes['text']);
            $attributes['account'] = $this->removeCRLF($attributes['account']);
            $attributes['email'] = $this->removeCRLF($attributes['email']);
            $attributes['default_amount'] = $this->removeCRLF($attributes['default_amount']);
            $attributes['background'] = $this->removeCRLF($attributes['background']);
            if(isset($attributes['field_name'])) $attributes['field_name'] = $this->removeCRLF($attributes['field_name']);
            if(isset($attributes['field_phone']))$attributes['field_phone'] = $this->removeCRLF($attributes['field_phone']);
            if(isset($attributes['field_email']))$attributes['field_email'] = $this->removeCRLF($attributes['field_email']);

            if($action == 'edit'){
                $hash = $this->removeCRLF(Yii::app()->request->getParam('h', ''));
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
                $attributes['hash'] =  md5(microtime(true).$salt);
                $attributes['password'] = md5($password.$salt);
            }
            //$model->photo = CUploadedFile::getInstance($model, 'photo');
            $attributes['photo'] = $this->removeCRLF($attributes['photo']);

            $ext = pathinfo($attributes['photo'], PATHINFO_EXTENSION);


            $types = array('jpg', 'gif', 'png', 'jpeg');
            if(!in_array($ext, $types)) {
                $model->addError('photo', 'Допустимые файлы: jpg, jpeg, gif, png');
            }

            $errors = $model->hasErrors();
            //@mail('paction@bk.ru', __LINE__.' debug '. date("Y-M-d H:i:s"), 'extention '.$ext. ', errors: '.$errors);

            $model->attributes = $attributes;

            if(!$errors && $model->validate())
            {
                if($model->save())
                {
                    if($model->photo && file_exists( Yii::getPathOfAlias('webroot').'/files/'.$model->photo))
                    {
                        $fileName = md5(time().'SaltHGYGSYGUKH86565').'.'.$ext;

                        rename(
                            Yii::getPathOfAlias('webroot').'/files/'.$model->photo,
                            Yii::getPathOfAlias('webroot').'/images/uploaded/'.$fileName
                        );
                        $attributes['photo'] = $fileName;
                        $model->photo = $fileName;
                        $model->save();
                        /*
                        $model->photo->saveAs(Yii::getPathOfAlias('webroot').'/images/uploaded/'.$fileName);
                        $model->photo = $fileName;
                        $model->save();*/
                    }
                    // test
                    /*$headers = 'From: info@money.yandex.ru' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();
                    @mail('iaaman@yamoney.ru', __LINE__.' test email '. date("Y-M-d H:i:s"), 'test', $headers);
                    @mail('eugenio@yamoney.ru', __LINE__.'test email '. date("Y-M-d H:i:s"), 'test', $headers);
                    @mail('paction@bk.ru', __LINE__.'test email '. date("Y-M-d H:i:s"), 'test', $headers);*/

                    if($action == 'add') {
                        //Send email via yiimailer documentation here: http://www.yiiframework.com/extension/yiimailer/

                        /*$mail = new YiiMailer();
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
                        $mail->setBcc("soberem@yamoney.ru");
                        $mail->setSubject('Пульт управления вашей страницей');
                        $mail->send();*/

                        $message = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<body style="margin:0; padding:0;">
	<table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;background-color:#F8F8F8;background-image: url(http://money.yandex.ru/img/html-letters/blank/bg_texture.png);" align="center">
		<tr>
			<td>
				<table cellpadding="0" cellspacing="0" width="640" style="border-collapse:collapse;" align="center">
					<tr><td height="20"/></tr>
					<tr>
						<td>
							<h1 style="margin-top:0;margin-bottom:20px;margin-left:35px;">
								<a href="https://money.yandex.ru/?_openstat=mail;collect;logo;" title="Яндекс.Деньги" target="_blank"><img src="https://money.yandex.ru/img/html-letters/blank/logo.png" width="109" height="44" alt="Яндекс.Деньги" style="border:none"/></a>
							</h1>
							<table cellpadding="0" cellspacing="0" width="640" style="border-collapse: collapse;">
								<tr>
									<td width="5"/>
									<td>
										<table cellpadding="0" cellspacing="0" width="632" style="border-collapse: collapse;">
											<tr>
												<td height="1" bgcolor="#E7E7E7" colspan="3"/>
											</tr>
											<tr>
												<td width="1" bgcolor="#E7E7E7"/>
												<td bgcolor="#FFFFFF" style="padding-top:30px;padding-right:30px;padding-bottom:20px;padding-left:30px;">
													<table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
														<tr>
															<td colspan="2">
																<h1 style="margin-top:0;margin-bottom:20px;font-weight:normal;font-family:Arial;font-size:20px;color:#000000;">
                                                                    '.$attributes['title'].'
																</h1>
															</td>
														</tr>
														<tr>
															<td width="520">
																<p style="color:#000000;font-family:Arial;font-size:14px;line-height:19px;margin-top:0;margin-bottom:8px;">Ура! Теперь для сбора денег у вас есть красивая персональная страница:</p>
																<p style="color:#000000;font-family:Arial;font-size:14px;line-height:19px;margin-top:0;margin-bottom:8px;">
																	 <a href="'.
                            $base . '/na/' . $attributes['page_address']
                            .'" target="_blank" style="text-decoration:none;"><font style="text-decoration:underline;" color="#0b55d9" face="Arial, Helvetica, sans-serif">'.
                            $base . '/na/' . $attributes['page_address']
                            .'</font></a>
																</p>
                                                                <p style="color:#000000;font-family:Arial;font-size:14px;line-height:19px;margin-top:0;margin-bottom:8px;">Пульт управления страницей — в этом письме. Советуем сохранить его, чтобы вы в любой момент могли изменить или удалить свою страницу.</p>
                                                                <p style="color:#000000;font-family:Arial;font-size:14px;line-height:19px;margin-top:0;margin-bottom:8px;">
                                                                    Секретное слово: <b>'.$password.'</b>
                                                                </p>
                                                                <p style="color:#000000;font-family:Arial;font-size:14px;line-height:19px;margin-top:0;margin-bottom:8px;">
                                                                    Изменить: <a href="'.
                            $base . '/admin/edit?h=' . $attributes['hash']
                            .'" target="_blank" style="text-decoration:none;"><font style="text-decoration:underline;" color="#0b55d9" face="Arial, Helvetica, sans-serif">'.
                            $base . '/admin/edit?h=' . $attributes['hash']
                            .'</font></a>
                                                                </p>
                                                                <p style="color:#000000;font-family:Arial;font-size:14px;line-height:19px;margin-top:0;margin-bottom:8px;">
                                                                    Удалить:  <a href="'.
                            $base . '/admin/delete?h=' . $attributes['hash']
                            .'" target="_blank" style="text-decoration:none;"><font style="text-decoration:underline;" color="#0b55d9" face="Arial, Helvetica, sans-serif">'.
                            $base . '/admin/delete?h=' . $attributes['hash']
                            .'</font></a>
                                                                </p>
															</td>
															<td width="50"/>
														</tr>
													</table>
													<table cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;">
														<tr><td colspan="2" height="25"/></tr>
														<tr><td colspan="2" bgcolor="#E5E5E5" height="1"/></tr>
														<tr><td colspan="2" height="18"/></tr>
														<tr>
															<td>
																<a href="https://money.yandex.ru/?_openstat=mail;collect;site;" style="color:#0b55d9;font-family:Arial;font-size:14px;" target="_blank">Команда Яндекс.Денег</a>
															</td>
															<td align="right">
																<table cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
																	<tr>
																		<td><span style="color:#999999;font-family:Arial;font-size:14px;line-height:19px;">Добавить нас в друзья:</span></td>
																		<td style="padding-top:3px;padding-left:5px;"><a href="https://vk.com/yamoney" target="_blank" style="margin-right:5px;"><img src="https://money.yandex.ru/img/html-letters/blank/vk.png" style="border:none;" alt="vkontakte" height="16" width="16"></a><a href="https://twitter.com/yamoneynews" target="_blank" style="margin-right:5px;"><img src="https://money.yandex.ru/img/html-letters/blank/tw.png" style="border:none;" alt="twitter" height="16" width="16"></a><a href="https://www.facebook.com/money.yandex.ru" target="_blank" style="margin-right:5px;"><img src="https://money.yandex.ru/img/html-letters/blank/fb.png" style="border:none;" alt="facebook" height="16" width="16"></a><a href="http://www.odnoklassniki.ru/yandexmoney" target="_blank" style="margin-right:5px;"><img src="https://money.yandex.ru/i/html-letters/od.png" style="border:none;" alt="odnoklassniki.ru" height="16" width="16"></a><a href="http://clubs.ya.ru/money/" target="_blank"><img src="https://money.yandex.ru/img/html-letters/blank/ya.png" style="border:none;" alt="ya.ru" height="16" width="16"></a></td>
																	</tr>
																</table>
															</td>
														</tr>
													</table>
												</td>
												<td width="1" bgcolor="#E7E7E7"/>
											</tr>
										</table>
									</td>
									<td width="3"/>
								</tr>
								<tr><td colspan="3"><img src="https://money.yandex.ru/img/html-letters/blank/shadow.png" width="640" height="16" alt=""/></td></tr>
							</table>
							<table cellpadding="0" cellspacing="0" width="630" style="border-collapse:collapse;margin-top:6px;">
								<tr>
									<td width="35"/>
									<td valign="top" width="270">
										<div style="color:#222222;font-family:Arial;font-size:14px;line-height:19px;margin-bottom:4px;">Есть вопросы?</div>
										<div style="color:#222222;font-family:Arial;font-size:12px;line-height:16px;margin-bottom:22px;">
											Загляните в раздел &laquo;<a href="https://money.yandex.ru/feedback/?_openstat=mail;collect;help;" target="_blank" style="color:#5f6d8a">Помощь</a>&raquo;. Или напишите нашей  <a href="https://money.yandex.ru/feedback/?_openstat=mail;collect;feedback;" target="_blank" style="color:#5f6d8a">службе поддержки</a>.
										</div>
									</td>
									<td width="30"/>
									<td valign="top" width="270">
										<div style="color:#222222;font-family:Arial;font-size:14px;line-height:19px;margin-bottom:4px;">
											Яндекс.Деньги в мобильном
										</div>
										<div style="color:#222222;font-family:Arial;font-size:12px;line-height:16px;">
											<a href="http://mobile.yandex.ru/money/?_openstat=mail;collect;mapps;" target="_blank" style="color:#5f6d8a">Скачайте приложения</a> для своего телефона или воспользуйтесь <a href="https://m.money.yandex.ru/?_openstat=mail;collect;mportal;" target="_blank" style="color:#5f6d8a">мобильной версией</a>.
										</div>
									</td>
									<td width="25"/>
								</tr>
							</table>
						</td>
					</tr>
					<tr><td height="50"/></tr>
				</table>
			</td>
		</tr>
	</table>
</body>';
                        $headers  = 'MIME-Version: 1.0' . "\r\n";
                        $headers .= 'From: info@money.yandex.ru' . "\r\n";
                        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                        $headers .= 'X-Mailer: PHP/' . phpversion();
                        $headers .= 'To: '.$attributes['email']. "\r\n";

                        @mail($attributes['email'], 'Пульт управления вашей страницей', $message, $headers);
                        @mail('soberem@yamoney.ru', 'Пульт управления вашей страницей', $message, $headers);

                    }

                    //$this->redirect('/site/ok');
                    $this->redirect('http://yasobe.ru/na/'.$attributes['page_address']);
                }
            }
        }


        $this->render('index',array(
            'model'=>$model,
            'baseUrl'=>$base,
            'hash'=>$this->removeCRLF(Yii::app()->request->getParam('h', md5(time().rand()))),
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

    /*public function actionMailtest()
    {
        $headers = 'From: info@money.yandex.ru' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        @mail('iaaman@yamoney.ru', 'test email '. date("Y-M-d H:i:s"), 'test', $headers);
        @mail('eugenio@yamoney.ru', 'test email '. date("Y-M-d H:i:s"), 'test', $headers);
        @mail('paction@bk.ru', 'test email '. date("Y-M-d H:i:s"), 'test', $headers);
    }*/

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

    protected function removeCRLF($string) {
        $newstring = '';
        $string = htmlspecialchars(strip_tags($string), ENT_QUOTES);
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