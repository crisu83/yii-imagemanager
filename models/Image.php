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
 *
 * The followings are the available model relations:
 * @property File $file
 */
class Image extends CActiveRecord
{
    // Most commonly used image formats.
    const FORMAT_GIF  = 'gif';
    const FORMAT_JPEG = 'jpeg';
    const FORMAT_JPG  = 'jpg';
    const FORMAT_PNG  = 'png';

    /**
     * @var string image manage component ID.
     */
    public $managerID = 'imageManager';

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
     * @return array the model relations.
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
            'id'     => 'ID',
            'fileId' => 'File',
            'width'  => 'Width',
            'height' => 'Height',
        );
    }

    /**
     * Returns the image instance associated with this model.
     * @return \Imagine\Image\ImageInterface the image.
     */
    public function openImage()
    {
        return $this->getManager()->openImage($this->file->resolvePath());
    }

    /**
     * Returns the full filename for this image.
     * @return string the filename.
     */
    public function resolveFilename()
    {
        return $this->file->resolveFilename();
    }

    /**
     * Returns the full path for this image.
     * @param boolean $absolute whether the path should be absolute (default to false).
     * @return string the path.
     */
    public function resolvePath($absolute = false)
    {
        return $this->file->resolvePath($absolute);
    }

    /**
     * Returns the url for this image.
     * @param boolean $absolute whether the url should be absolute (default to false).
     * @return string the path.
     */
    public function resolveUrl($absolute = false)
    {
        return $this->file->resolveUrl($absolute);
    }

    /**
     * Returns the image file path.
     * @return string the path.
     */
    public function resolveNormalizedPath()
    {
        return $this->getManager()->normalizePath($this->resolvePath());
    }

    /**
     * Returns the image manager application component.
     * @return ImageManager component instance.
     */
    public function getManager()
    {
        return Yii::app()->getComponent($this->managerID);
    }
}