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
     * @var string the image manager component id.
     */
    public $componentID = 'imageManager';

    /** @var ImageManager */
    private $_imageManager;

    /**
     * Creates a new image preset from an existing image.
     * @param string $name the preset name.
     * @param integer $id the model id.
     * @param string $format the image format.
     */
    public function actionPreset($name, $id, $format)
    {
        $image = $this->getImageManager()->createPresetImage($name, $id, $format);
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
     * Returns the image manager component.
     * @return ImageManager the component.
     * @throws CException if the component is not found.
     */
    protected function getImageManager()
    {
        if ($this->_imageManager !== null) {
            return $this->_imageManager;
        } else {
            if (($imageManager = Yii::app()->getComponent($this->componentID)) == null) {
                throw new CException('Failed to get image manager. Component not found.');
            }
            return $this->_imageManager = $imageManager;
        }
    }
}