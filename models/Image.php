<?php

/**
 * This is the model class for table "image".
 *
 * The followings are the available columns in table 'image':
 * @property string $id
 * @property integer $fileId
 * @property integer $width
 * @property integer $height
 *
 * The followings are the available relations for table 'image':
 * @property File $file
 */
class Image extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Image the static model class
	 */
	public static function model($className = __CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'image';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('fileId, width, height', 'required'),
			array('fileId, width, height', 'numerical', 'integerOnly' => true),
			// The following rule is used by search().
			array('id, fileId, width, height', 'safe', 'on' => 'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'file' => array(self::BELONGS_TO, 'File', 'fileId'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('label', 'ID'),
			'fileId' => Yii::t('label', 'File'),
			'width' => Yii::t('label', 'Width'),
			'height' => Yii::t('label', 'Height'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('fileId', $this->fileId);
		$criteria->compare('width', $this->width);
		$criteria->compare('height', $this->height);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}