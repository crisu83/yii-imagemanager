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
     * @var string name to save the image with.
     */
    public $name;

    /**
     * @var string internal path for saving the image.
     */
    public $path;

    /**
     * @var string the name of the image id column.
     */
    public $idAttribute = 'imageId';

    /**
     * @var string name of the model attribute that holds the uploaded file (defaults to 'upload').
     */
    public $uploadAttribute = 'upload';

    /**
     * @var boolean whether to save the image automatically when saving the owner model.
     */
    public $autoSave = true;

    /**
     * @var boolean whether to delete the image automatically when deleting the owner model.
     */
    public $autoDelete = true;

    /**
     * @var string the application component id for the image manager (defaults to 'imageManager').
     */
    public $managerID = 'imageManager';

    /**
     * Actions to take before validating the owner of this behavior.
     * @param CModelEvent $event event parameter.
     */
    public function beforeValidate($event)
    {
        if ($this->autoSave) {
            $this->owner->{$this->uploadAttribute} = $this->getUploadedImage();
        }
    }

    /**
     * Actions to take before saving the owner of this behavior.
     * @param CModelEvent $event event parameter.
     */
    public function beforeSave($event)
    {
        if ($this->autoSave) {
            $this->saveImage($this->owner->{$this->uploadAttribute}, $this->name, $this->path);
        }
    }

    /**
     * Actions to take before deleting the owner of this behavior.
     * @param CModelEvent $event event parameter.
     */
    public function beforeDelete($event)
    {
        if ($this->autoDelete) {
            $this->deleteImage();
        }
    }

    /**
     * Returns the uploaded image file.
     * @return CUploadedFile the file.
     */
    public function getUploadedImage()
    {
        return CUploadedFile::getInstance($this->owner, $this->uploadAttribute);
    }

    /**
     * Saves the image for the owner of this behavior.
     * @param CUploadedFile $file the uploaded file.
     * @param string $name the image name.
     * @param string $path the path for saving the image.
     * @param string $scenario name of the scenario.
     * @return Image the model.
     */
    public function saveImage($file, $name = null, $path = null, $scenario = 'insert')
    {
        $model = $this->getManager()->saveModel(new UploadedFile($file), $name, $path, $scenario);
        $this->owner->{$this->idAttribute} = $model->id;
        return $model;
    }

    /**
     * Loads the image associated with the owner of this behavior.
     * @param mixed $with related models that should be eager-loaded.
     * @return Image the model.
     */
    public function loadImage($with = 'file')
    {
        return $this->getManager()->loadModel($this->owner->{$this->idAttribute}, $with);
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
     * @throws CException if model loading fails.
     */
    public function createImagePresetUrl($name)
    {
        $manager = $this->getManager();
        if (($model = $manager->loadModel($this->owner->{$this->idAttribute}, 'file')) === null) {
            throw new CException(sprintf(
                'Failed to locate image model with id "%d".',
                $this->owner->{$this->idAttribute})
            );
        }
        return $manager->createImagePresetUrl($model, $manager->loadPreset($name));
    }

    /**
     * Returns the HTML attributes to the image for the owner of this behavior.
     * @param string $name the preset name.
     * @param string $holder the placeholder name.
     * @return string the url.
     */
    public function createImagePresetOptions($name, $holder = null)
    {
        $manager = $this->getManager();
        $model = !empty($this->owner->{$this->idAttribute})
            ? $manager->loadModel($this->owner->{$this->idAttribute}, 'file')
            : null;
        return $manager->createPresetOptions($name, $model, $holder);
    }

    /**
     * Deletes the image for the owner of this behavior.
     * @throws CException if the image cannot be deleted or if the image id cannot be removed from the owner model.
     */
    public function deleteImage()
    {
        if (!$this->getManager()->deleteModel($this->owner->{$this->idAttribute})) {
            throw new CException('Failed to delete image.');
        }
        $this->owner->{$this->idAttribute} = null;
        if (!$this->owner->save(false)) {
            throw new CException('Failed to remove image id from owner.');
        }
    }

    /**
     * Returns the image manager component instance.
     * @return ImageManager the component.
     */
    protected function getManager()
    {
        return Yii::app()->getComponent($this->managerID);
    }
}