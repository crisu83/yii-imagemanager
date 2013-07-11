<?php
/**
 * Image class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.models
 */

/**
 * This is the model class for table "image".
 *
 * The followings are the available columns in table 'image':
 * @property string $id
 * @property integer $fileId
 * @property integer $width
 * @property integer $height
 */
class Image extends CActiveRecord
{
    /** @var ImageManager */
    private $_manager;

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
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'     => Yii::t('label', 'ID'),
            'fileId' => Yii::t('label', 'File'),
            'width'  => Yii::t('label', 'Width'),
            'height' => Yii::t('label', 'Height'),
        );
    }

    /**
     * Returns the full filename for this file.
     * @return string the filename.
     */
    public function resolveFilename()
    {
        $file = $this->getFile();
        return $file->name . '-' . $this->id . '.' . $file->extension;
    }

    /**
     * Returns the image file path.
     * @return string the path.
     */
    public function resolveFilePath()
    {
        $file = $this->getFile();
        $path = $file->getPath() . $this->resolveFilename();
        return $this->_manager->normalizePath($path);
    }

    /**
     * Returns the associated file model.
     * @return File the model.
     */
    public function getFile()
    {
        return $this->_manager->getFileManager()->loadModel($this->fileId);
    }

    /**
     * Sets the image manager.
     * @param ImageManager $manager the component.
     */
    public function setManager($manager)
    {
        $this->_manager = $manager;
    }
}