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
     *     array('thumbnail', 'width' => 160, 'height' => 90),
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
     * @var string the name of the image model class.
     */
    public $modelClass = 'Image';
    /**
     * @var string the component id for the file manager.
     */
    public $fileManagerID = 'fileManager';

    /** @var FileManager */
    private $_fileManager;
    /** @var ImagineFilterChain[] */
    private $_filterChains;
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
        $this->initFilterChains();
    }

    /**
     * Creates filter chains from the presets.
     */
    protected function initFilterChains()
    {
        $this->_filterChains = array();
        foreach ($this->presets as $name => $filters) {
            $this->_filterChains[$name] = ImagineFilterChain::create($filters);
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
        $model     = $this->loadModel($id);
        $cacheUrl  = $this->resolvePresetCacheUrl($name);
        $imagePath = $model->resolveFilePath();
        return $cacheUrl . $imagePath;
    }

    /**
     * Creates an image preset for the image model with the given id.
     * @param integer $id the model id.
     * @param string $name the preset name.
     * @return ImageInterface the image.
     * @throws CException if the preset name is invalid.
     */
    public function createPreset($id, $name)
    {
        if (!isset($this->presets[$name])) {
            throw new CException('Failed to create preset.');
        }
        $filter    = $this->getPresetFilterChain($name);
        $model     = $this->loadModel($id);
        $file      = $model->getFile();
        $rawPath   = $file->resolvePath();
        $image     = $this->openImage($rawPath);
        $image     = $filter->apply($image);
        $path      = $file->getPath();
        $path      = $this->normalizePath($path);
        $cachePath = $this->resolvePresetCachePath($name) . $path;
        $this->getFileManager()->createDirectory($cachePath);
        $cached = $cachePath . $file->resolveFilename();
        $image->save($cached);
        $image->show($file->extension);
    }

    /**
     * Returns the pre-configured filter chain for a specific preset.
     * @param string $name the preset name.
     * @return ImagineFilterChain the filter chain.
     */
    protected function getPresetFilterChain($name)
    {
        return isset($this->_filterChains[$name]) ? $this->_filterChains[$name] : null;
    }

    /**
     * Returns the path for a cached image preset.
     * @param string $name the preset name.
     * @param boolean $absolute whether to return an absolute path.
     * @return string the path.
     */
    protected function resolvePresetCachePath($name, $absolute = true)
    {
        $checksum = $this->calculateCacheChecksum($this->presets[$name]);
        return $this->resolveCachePath($absolute) . $name . '-' . $checksum . '/';
    }

    /**
     * Returns the url to a cached image preset.
     * @param string $name the preset name.
     * @param boolean $absolute whether to return an absolute url.
     * @return string the url.
     */
    protected function resolvePresetCacheUrl($name, $absolute = true)
    {
        $checksum = $this->calculateCacheChecksum($this->presets[$name]);
        return $this->resolveCacheUrl($absolute) . $name . '-' . $checksum . '/';
    }

    /**
     * Calculates the checksum from the given preset configuration.
     * @param array $config the preset configuration.
     * @return string the checksum.
     */
    protected function calculateCacheChecksum($config)
    {
        return md5(CJSON::encode($config));
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
     * @param CUploadedFile $file
     * @param string $name the file name.
     * @param string $path the file path.
     * @return Image the image model.
     * @throws CException
     */
    public function saveModel($file, $name = null, $path = null)
    {
        $fileManager = $this->getFileManager();
        $path        = $this->resolveRawPath() . $path;
        $file        = $fileManager->saveModel($file, $name, $path);
        /* @var Image $model */
        $model = new $this->modelClass();
        $model->setManager($this);
        $savePath      = $file->resolvePath();
        $image         = $this->openImage($savePath);
        $model->fileId = $file->id;
        $size          = $image->getSize();
        $model->width  = $size->getWidth();
        $model->height = $size->getHeight();
        if (!$model->save()) {
            throw new CException('Failed to save image. Database record could not be saved.');
        }
        return $model;
    }

    /**
     * Loads an image model.
     * @param integer $id the model id.
     * @return Image the model.
     */
    public function loadModel($id)
    {
        /* @var Image $model */
        $model = CActiveRecord::model($this->modelClass)->findByPk($id);
        if ($model === null) {
            throw new CException('Failed to load image model.');
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
        return $path . $this->imageDir . '/' . $this->rawDir . '/';
    }

    /**
     * Returns the path to the cached images.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function resolveCachePath($absolute = false)
    {
        $path = $absolute ? $this->getFileManager()->getBasePath(true) : '';
        return $path . $this->imageDir . '/' . $this->cacheDir . '/';
    }

    /**
     * Returns the url to the cached images.
     * @param boolean $absolute whether the url should be absolute.
     * @return string the url.
     */
    public function resolveCacheUrl($absolute = false)
    {
        $url = $absolute ? $this->getFileManager()->getBaseUrl(true) : '';
        return $url . $this->imageDir . '/' . $this->cacheDir . '/';
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
