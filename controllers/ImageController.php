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
        $manager = $this->getImageManager();
        $image = $manager->loadModelByFileId($fileId);
        $preset = $manager->createPresetImage($name, $image, $format);
        $preset->show($format);
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
        $image = $this->getImageManager()->createPresetHolder($preset, $name, $format);
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
        $model = $this->getImageManager()->loadModel($id);
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
        $manager = $this->getImageManager();
        $model = $manager->saveModel($file, $saveName, $path);
        $ajax->add('imageId', $model->id);
        if ($preset !== null) {
            $preset = $manager->loadPreset($preset);
            $ajax->add('imageUrl', $manager->createImagePresetUrl($model->id, $preset));
        }
        $ajax->success();
    }

    /**
     * Returns the image manager component.
     * @return ImageManager the component.
     * @throws CException if the component is not found.
     */
    protected function getImageManager()
    {
        if (($imageManager = Yii::app()->getComponent($this->managerID)) == null) {
            throw new CException(sprintf(
                'Failed to get the image manager component. Application component "%" does not exist.',
                $this->managerID
            ));
        }
        return $imageManager;
    }
}