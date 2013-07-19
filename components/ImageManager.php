<?php
/**
 * ImageManager class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.components
 */

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;

// Let Yii's autoloader know where to find the Imagine classes.
Yii::setPathOfAlias('Imagine', Yii::getPathOfAlias('vendor.imagine.imagine.lib.Imagine'));

// Import some dependencies.
Yii::import('vendor.crisu83.yii-extension.behaviors.ComponentBehavior');
Yii::import('vendor.crisu83.yii-filemanager.models.File');

/**
 * Application component for managing images.
 *
 * @method createPathAlias($alias, $path) via ComponentBehavior
 * @method import($alias) via ComponentBehavior
 */
class ImageManager extends CApplicationComponent
{
    // Supported image drivers.
    const DRIVER_GD      = 'gd';
    const DRIVER_IMAGICK = 'imagick';
    const DRIVER_GMAGICK = 'gmagick';

    /**
     * @var string the image driver to use.
     */
    public $driver = self::DRIVER_GD;
    /**
     * @var array the preset filter configurations.
     *
     * Example usage:
     *
     * 'presets' => array(
     *   'myPreset' => array(
     *     'allowCache' => true,
     *     'filters' => array(
     *        array('thumbnail', 'width' => 160, 'height' => 90),
     *     ),
     *   ),
     * ),
     */
    public $presets = array();
    /**
     * @var string the name of the images directory.
     */
    public $imageDir = 'images';
    /**
     * @var string the name of the directory with the unmodified images.
     */
    public $rawDir = 'raw';
    /**
     * @var string the name of the directory with the modified or cached images.
     */
    public $cacheDir = 'cache';
    /**
     * @var string @todo
     */
    public $createPresetRoute = 'image/createPreset';
    /**
     * @var string the name of the image model class.
     */
    public $modelClass = 'Image';
    /**
     * @var string the component id for the file manager.
     */
    public $fileManagerID = 'fileManager';

    /** @var FileManager */
    private $_fileManager;
    /** @var ImagePreset[] */
    private $_presets;
    /** @var ImagineInterface */
    private $_factory;

    /**
     * Initializes the component.
     */
    public function init()
    {
        parent::init();
        $this->attachBehavior('ext', new ComponentBehavior);
        $this->createPathAlias('imageManager', __DIR__ . '/..');
        $this->import('filters.*');
        $this->import('models.*');
        $this->initPresets();
    }

    /**
     * Initializes the image presets if applicable.
     */
    protected function initPresets()
    {
        $this->_presets = array();
        if (!empty($this->presets))
        {
            foreach ($this->presets as $name => $config) {
                if (!isset($config['class'])) {
                    $config['class'] = 'imageManager.components.ImagePreset';
                }
                $config['name'] = $name;
                $preset = Yii::createComponent($config);
                $preset->init();
                $preset->setManager($this);
                $this->_presets[$name] = $preset;
            }
        }
    }

    /**
     * Creates the url for a specific image preset.
     * @param integer $id the model id.
     * @param string $name the preset name.
     * @return string the url.
     */
    public function createPresetUrl($id, $name)
    {
        $preset = $this->getPreset($name);
        if (!$preset->allowCache) {
            $params = array('id' => $id, 'name' => $name);
            $url = $this->resolveCreateImageUrl($params);
        } else {
            $model     = $this->loadModel($id);
            $cacheUrl  = $preset->resolveCacheUrl();
            $imagePath = $model->resolveFilePath();
            $url = $cacheUrl . $imagePath;
        }
        return $url;
    }

    /**
     * Creates a preset image for the image model with the given id.
     * @param integer $id the model id.
     * @param string $name the preset name.
     * @return ImageInterface the image.
     * @throws CException if the preset name is invalid.
     */
    public function createPresetImage($id, $name)
    {
        $preset = $this->getPreset($name);
        if ($preset === null) {
            throw new CException(sprintf('Failed to create preset image. Preset "%s" not defined.', $name));
        }
        $filterChain = $preset->getFilterChain();
        $model       = $this->loadModel($id);
        $file        = $model->getFile();
        $rawPath     = $file->resolvePath();
        $image       = $this->openImage($rawPath);
        $image       = $filterChain->apply($image);
        $filePath    = $this->normalizePath($file->getPath());
        $cachePath   = $preset->resolveCachePath() . $filePath;
        $this->getFileManager()->createDirectory($cachePath);
        $cached = $cachePath . $file->resolveFilename();
        // todo: now the file is always generated, consider adding support for using the existing file in some cases.
        $image->save($cached);
        $image->show($file->extension);
    }

    /**
     * Normalizes the given path by removing the raw path.
     * @param string $path the path to normalize.
     * @return string the path.
     */
    public function normalizePath($path)
    {
        return str_replace($this->resolveRawPath(false), '', $path);
    }

    /**
     * Saves an image file on the hard drive and in the database.
     * @param CUploadedFile $file the uploaded file instance.
     * @param string $name the file name.
     * @param string $path the file path.
     * @return Image the image model.
     * @throws CException if saving the image model is not successful.
     */
    public function saveModel($file, $name = null, $path = null)
    {
        /* @var Image $model */
        $model = new $this->modelClass();
        $model->setManager($this);
        $fileManager   = $this->getFileManager();
        $path          = $this->resolveRawPath() . $path;
        $file          = $fileManager->saveModel($file, $name, $path);
        $savePath      = $file->resolvePath();
        $image         = $this->openImage($savePath);
        $model->fileId = $file->id;
        $size          = $image->getSize();
        $model->width  = $size->getWidth();
        $model->height = $size->getHeight();
        if (!$model->save()) {
            throw new CException('Failed to save image model. Database record could not be saved.');
        }
        return $model;
    }

    /**
     * Loads an image model.
     * @param integer $id the model id.
     * @return Image the model.
     * @throws CException if the image model is not found.
     */
    public function loadModel($id)
    {
        /* @var Image $model */
        $model = CActiveRecord::model($this->modelClass)->findByPk($id);
        if ($model === null) {
            throw new CException('Failed to load image model. Record not found.');
        }
        $model->setManager($this);
        return $model;
    }

    /**
     * Deletes an image model.
     * @param integer $id the model id.
     * @return boolean the result.
     */
    public function deleteModel($id)
    {
        $model = $this->loadModel($id);
        return $model->delete();
    }

    /**
     * Opens an image through Imagine.
     * @param string $path the image path.
     * @return \Imagine\Image\ImageInterface
     */
    public function openImage($path)
    {
        return $this->getFactory()->open($path);
    }

    /**
     * Returns the path to the raw images.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function resolveRawPath($absolute = false)
    {
        $path = $absolute ? $this->getFileManager()->getBasePath() : '';
        return $path . $this->imageDir . DIRECTORY_SEPARATOR . $this->rawDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the path to the cached images.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function resolveCachePath($absolute = false)
    {
        return $this->getBasePath($absolute) . $this->cacheDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the url to the cached images.
     * @param boolean $absolute whether the url should be absolute.
     * @return string the url.
     */
    public function resolveCacheUrl($absolute = false)
    {
        return $this->getBaseUrl($absolute) . $this->cacheDir . '/';
    }

    /**
     * Returns the url for creating an image preset.
     * @param array $params additional GET parameters.
     * @return string the url.
     */
    public function resolveCreateImageUrl($params = array())
    {
        return Yii::app()->createUrl($this->createPresetRoute, $params);
    }

    /**
     * Returns the url to the images folder.
     * @param boolean $absolute whether to return an absolute url.
     * @return string the url.
     */
    public function getBaseUrl($absolute = false)
    {
        $path = $absolute ? $this->getFileManager()->getBaseUrl($absolute) : '';
        return $path . $this->imageDir . '/';
    }

    /**
     * Returns the path to the images folder.
     * @param boolean $absolute whether to return an absolute path.
     * @return string the path.
     */
    public function getBasePath($absolute = true)
    {
        $url = $absolute ? $this->getFileManager()->getBasePath($absolute) : '';
        return $url . $this->imageDir . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the preset component with the given name.
     * @param string $name the preset name.
     * @return ImagePreset the component.
     */
    public function getPreset($name)
    {
        return isset($this->_presets[$name]) ? $this->_presets[$name] : null;
    }

    /**
     * Returns the Imagine factory.
     * @return ImagineInterface the factory.
     */
    public function getFactory()
    {
        if (isset($this->_factory)) {
            return $this->_factory;
        } else {
            return $this->_factory = $this->createFactory($this->driver);
        }
    }

    /**
     * Creates the Imagine factory for the given image driver.
     * @param string $driver the image driver.
     * @return ImagineInterface the factory.
     * @throws CException if the driver is invalid
     */
    protected function createFactory($driver)
    {
        switch ($driver) {
            case self::DRIVER_GD:
                return new Imagine\Gd\Imagine();
            case self::DRIVER_IMAGICK:
                return new Imagine\Imagick\Imagine();
            case self::DRIVER_GMAGICK:
                return new Imagine\Gmagick\Imagine();
            default:
                throw new CException('Failed to create factory. Driver not found.');
        }
    }

    /**
     * Returns the file manager component.
     * @return FileManager the component.
     * @throws CException if the component is not found.
     */
    public function getFileManager()
    {
        if (isset($this->_fileManager)) {
            return $this->_fileManager;
        } else {
            if (!Yii::app()->hasComponent($this->fileManagerID)) {
                throw new CException('Failed to get file manager. Application component could not be found.');
            }
            return $this->_fileManager = Yii::app()->getComponent($this->fileManagerID);
        }
    }
}
