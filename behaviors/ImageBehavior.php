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
 *
 * @property CActiveRecord $owner
 */
class ImageBehavior extends CActiveRecordBehavior
{
    /**
     * @var string the name of the image id column.
     */
    public $idAttribute = 'imageId';

    /**
     * @var string name of the model attribute that holds the uploaded file (defaults to 'upload').
     */
    public $uploadAttribute = 'upload';

    /**
     * @var string the application component id for the image manager.
     */
    public $componentID = 'imageManager';

    /**
     * Loads the image associated with the owner of this behavior.
     * @param int $id the model id.
     * @return Image the model.
     */
    public function loadImage()
    {
        return $this->getImageManager()->loadModel($this->resolveImageId());
    }

    /**
     * Saves the image for the owner of this behavior.
     * @param string $name the image name.
     * @param string $path the path for saving the image.
     * @param array $saveAttributes attributes that should be passed to the save method.
     * @param string $scenario name of the scenario.
     * @param CUploadedFile $file the uploaded file.
     * @return Image the model.
     */
    public function saveImage($name = null, $path = null, $saveAttributes = array(), $scenario = 'insert')
    {
        $this->owner->{$this->uploadAttribute} = CUploadedFile::getInstance(
            $this->owner,
            $this->uploadAttribute
        );
        if (!in_array($this->uploadAttribute, $saveAttributes)) {
            $saveAttributes[] = $this->uploadAttribute;
        }
        if (!$this->owner->validate($saveAttributes)) {
            throw new CException('Failed to save image.');
        }
        $model = $this->getImageManager()->saveModel($this->owner->{$this->uploadAttribute}, $name, $path, $scenario);
        $this->owner->{$this->idAttribute} = $model->id;
        if (!$this->owner->save(true, array($this->idAttribute))) {
            throw new CException('Failed to save image id to owner.');
        }
        return $model;
    }

    /**
     * Render the image for the owner of this behavior.
     * @param string $name the preset name.
     * @param string $alt the alternative text display.
     * @param array $htmlOptions additional HTML attributes.
     * @param string $holder the placeholder name.
     * @return string the rendered image.
     */
    public function renderImagePreset($name, $alt = '', $htmlOptions = array(), $holder = null)
    {
        $htmlOptions = array_merge($htmlOptions, $this->createImagePresetOptions($name, $holder));
        $src = isset($htmlOptions['src']) ? $htmlOptions['src'] : '';
        return CHtml::image($src, $alt, $htmlOptions);
    }

    /**
     * Returns the url to the image for the owner of this behavior.
     * @param string $name the preset name.
     * @return string the url.
     */
    public function createImagePresetUrl($name)
    {
        $manager = $this->getImageManager();
        return $manager->createImagePresetUrl($this->owner->{$this->idAttribute}, $manager->loadPreset($name));
    }

    /**
     * Returns the HTML attributes to the image for the owner of this behavior.
     * @param string $name the preset name.
     * @param string $holder the placeholder name.
     * @return string the url.
     */
    public function createImagePresetOptions($name, $holder = null)
    {
        return $this->getImageManager()->createPresetOptions($name, $this->owner->{$this->idAttribute}, $holder);
    }

    /**
     * Deletes the image for the owner of this behavior.
     * @throws CException if the image model cannot be deleted.
     */
    public function deleteImage()
    {
        if (!$this->getImageManager()->deleteModel($this->resolveImageId())) {
            throw new CException('Failed to delete image.');
        }
        if (!$this->owner->save(false)) {
            throw new CException('Failed to remove image id from owner.');
        }
    }

    /**
     * Saves the model id for the associated image on the owner of this behavior.
     * @param int $imageId the image id.
     * @param array $saveAttributes attributes that should be passed to the save method.
     * @throws CException if the owner cannot be saved.
     */
    protected function saveImageId($imageId, $saveAttributes = array())
    {
        if (!in_array($this->idAttribute, $saveAttributes)) {
            $saveAttributes[] = $this->idAttribute;
        }
        $this->owner->{$this->idAttribute} = $imageId;
        if (!$this->owner->save(true, $saveAttributes)) {
            throw new CException('Failed to save image id to owner.');
        }
    }

    /**
     * Returns the model id for the image associated with the owner of this behavior.
     * @return int the model id.
     * @throws CException if the owner is not associated with an image.
     */
    protected function resolveImageId()
    {
        if (($imageId = $this->owner->{$this->idAttribute}) !== null) {
            return $imageId;
        }
        throw new CException('Owner is not associated with an image.');
    }

    /**
     * Returns the image manager component instance.
     * @return ImageManager the component.
     */
    protected function getImageManager()
    {
        return Yii::app()->getComponent($this->componentID);
    }
}