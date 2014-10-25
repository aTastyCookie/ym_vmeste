<?php

/**
 * This is the model class for table "pages".
 *
 * The followings are the available columns in table 'pages':
 * @property integer $id
 * @property string $title
 * @property string $text
 * @property integer $default_amount
 * @property integer $account
 * @property string $page_address
 * @property integer $field_name
 * @property integer $field_phone
 * @property integer $field_email
 * @property string $background
 * @property string $photo
 * @property string $email
 * @property string $createtime
 * @property integer $status
 * @property string $hash
 * @property string $password
 */
class Pages extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'pages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('title, text, default_amount, account, page_address, email, hash, password', 'required'),
			array('page_address, hash', 'unique'),
			array('default_amount, account, field_name, field_phone, field_email, status', 'numerical', 'integerOnly'=>true),
			array('title, page_address, background, email, hash, password', 'length', 'max'=>255),
			array('email, hash, password', 'length', 'min'=>5),
			array('page_address', 'length', 'min'=>1),
            array('photo', 'safe'),
            //array('photo', 'file', 'types'=>'jpg, gif, png','allowEmpty'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, title, text, default_amount, account, page_address, field_name, field_phone, field_email, background, photo, email, createtime, status, hash, password', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'title' => 'Заголовок',
			'text' => 'Текст',
			'default_amount' => ' Сумма по умолчанию',
			'account' => 'Номер счета в Яндекс.Деньгах',
			'page_address' => 'Адрес страницы',
			'field_name' => 'Указать ФИО',
			'field_phone' => 'Указать Телефон',
			'field_email' => 'Указать Email',
			'background' => 'Фон',
			'photo' => 'Картинка',
			'email' => 'Email',
			'createtime' => 'Createtime',
			'status' => 'Status',
			'hash' => 'Hash',
			'password' => 'Password',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('text',$this->text,true);
		$criteria->compare('default_amount',$this->default_amount);
		$criteria->compare('account',$this->account);
		$criteria->compare('page_address',$this->page_address,true);
		$criteria->compare('field_name',$this->field_name);
		$criteria->compare('field_phone',$this->field_phone);
		$criteria->compare('field_email',$this->field_email);
		$criteria->compare('background',$this->background,true);
		$criteria->compare('photo',$this->photo,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('createtime',$this->createtime,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('hash',$this->hash,true);
		$criteria->compare('password',$this->password,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Pages the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
