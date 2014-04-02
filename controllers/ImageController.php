<?php
/**
 * ImageController class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.controllers
 */

/**
 * Controller class for image actions.
 */
class ImageController extends CController
{
    /**
     * @var string image manager component id.
     */
    public $managerID = 'imageManager';

    /**
     * Returns the filter configurations.
     * @return array a list of filter configurations.
     */
    public function filters()
    {
        return array(
            'ajaxUpload + ajaxOnly',
        );
    }

    /**
     * Creates a new image preset from an existing image.
     * @param string $name the preset name.
     * @param integer $fileId the model id.
     * @param string $format the image format.
     */
    public function actionPreset($name, $fileId, $format)
    {
        $manager = $this->getManager();
        $model = $manager->loadModelByFileId($fileId);
        $preset = $manager->loadPreset($name);
        $image = $manager->createPresetImage($preset, $model, $format);
        $image->show($format);
        Yii::app()->end();
    }

    /**
     * Creates a new placeholder image preset.
     * @param string $name the placeholder name.
     * @param string $preset the preset name.
     * @param string $format the image format.
     */
    public function actionHolder($name, $preset, $format = Image::FORMAT_PNG)
    {
        $image = $this->getManager()->createPresetHolder($preset, $name);
        $image->show($format);
        Yii::app()->end();
    }

    /**
     * Creates a new image by filtering an existing image.
     * @param integer $id the model id.
     * @param string $format the image format.
     * @throws CException if a required parameters is missing.
     */
    public function actionFilter($id, $format)
    {
        if (!isset($_GET['config'])) {
            throw new CException('You have to provide a "config" parameter.');
        }
        if (($model = $this->getManager()->loadModel($id, 'file')) === null) {
            throw new CException(sprintf('Failed to locate image model with id "%d".', $id));
        }
        $image = $model->openImage();
        $preset = ImagePreset::create(array('filters' => $_GET['config']));
        $image = $preset->applyFilters($image);
        $image->show($format);
    }

    /**
     * Action for uploading an image using AJAX.
     * @param string $name name for locating the uploaded file.
     * @param string $preset name of the preset.
     * @param string $saveName image name.
     * @param string $path image path.
     * @throws CException if the uploaded file is not found.
     */
    public function actionAjaxUpload($name, $preset = null, $saveName = null, $path = null)
    {
        $ajax = new AjaxResponse;
        $file = CUploadedFile::getInstanceByName($name);
        if ($file === null) {
            $ajax->error(sprintf('Uploaded file with name "%s" could not be found.', $name));
        }
        $manager = $this->getManager();
        try {
            $model = $manager->saveModel(new UploadedFile($file), $saveName, $path);
            $ajax->add('imageId', $model->id);
            if ($preset !== null) {
                $preset = $manager->loadPreset($preset);
                $ajax->add(
                    'imageTooSmall',
                    $preset->getWidth() > $model->width || $preset->getHeight() > $model->height
                );
                $ajax->add('imageUrl', $manager->createImagePresetUrl($model, $preset));
            }
            $ajax->success();
        } catch (Exception $e) {
            Yii::log(
                sprintf('Image upload failed with error: %s', $e->getMessage()),
                CLogger::LEVEL_ERROR,
                'ImageManager'
            );
            $ajax->error(t('imageManager', 'Something went wrong when uploading the image, please try again.'));
        }
    }

    /**
     * Returns the image manager component.
     * @return ImageManager the component.
     * @throws CException if the component is not found.
     */
    protected function getManager()
    {
        if (($manager = Yii::app()->getComponent($this->managerID)) == null) {
            throw new CException(sprintf(
                'Failed to get the image manager component. Application component "%" does not exist.',
                $this->managerID
            ));
        }
        return $manager;
    }
}