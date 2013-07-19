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
     * @var string the default controller action.
     */
    public $defaultAction = 'create';
    /**
     * @var string the image manager component id.
     */
    public $componentID = 'imageManager';

    /** @var ImageManager */
    private $_imageManager;

    /**
     * Creates a new image preset.
     * @param integer $id the model id.
     * @param string $name the preset name.
     */
    public function actionCreatePreset($id, $name)
    {
        $this->getImageManager()->createPresetImage($id, $name);
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