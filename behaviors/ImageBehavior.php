<?php
/**
 * ImageBehavior class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.behaviors
 */

/**
 * Active record behavior for saving, loading, rendering and deleting associated image models.
 */
class ImageBehavior extends CActiveRecordBehavior
{
    /**
     * @var string the application component id for the image manager.
     */
    public $componentID = 'imageManager';
    /**
     * @var string the name of the image id column.
     */
    public $idAttribute = 'imageId';

    /** @var ImageManager */
    private $_imageManager;

    /**
     * Saves the image for the owner of this behavior.
     * @param string $name the image name.
     * @param string $path the path for saving the image.
     * @param CUploadedFile $file the uploaded file.
     * @return Image the model.
     */
    public function saveImage($file, $name = null, $path = null)
    {
        $model = $this->getImageManager()->saveModel($file, $name, $path);
        if ($model === null) {
            return null;
        }
        $this->owner->{$this->idAttribute} = $model->id;
        $this->owner->save(true, array($this->idAttribute));
        return $model;
    }

    /**
     * Render the image for the owner of this behavior.
     * @param string $name the preset name.
     * @param string $alt the alternative text display.
     * @param array $htmlOptions additional HTML attributes.
     * @param string $placeholder the placeholder image.
     * @return string the rendered image.
     */
    public function renderImagePreset($name, $alt = '', $htmlOptions = array(), $placeholder = '´&nbsp;')
    {
        return !empty($this->owner->{$this->idAttribute})
            ? CHtml::image($this->createImagePresetUrl($name), $alt, $htmlOptions)
            : $placeholder;
    }

    /**
     * Returns the url to the image for the owner of this behavior.
     * @param string $name the preset name.
     * @return string the url.
     */
    public function createImagePresetUrl($name)
    {
        return $this->getImageManager()->createPresetUrl($this->owner->{$this->idAttribute}, $name);
    }

    /**
     * Deletes the image for the owner of this behavior.
     * @return boolean whether the image was deleted.
     */
    public function deleteImage()
    {
        if ($this->getImageManager()->deleteModel($this->owner{$this->idAttribute})) {
            $this->owner->{$this->idAttribute} = null;
            return $this->owner->save(true, array($this->idAttribute));
        }
        return false;
    }

    /**
     * Returns the image manager component instance.
     * @return ImageManager the component.
     */
    protected function getImageManager()
    {
        if (isset($this->_imageManager)) {
            return $this->_imageManager;
        } else {
            return $this->_imageManager = Yii::app()->getComponent($this->componentID);
        }
    }
}