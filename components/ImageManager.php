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

/**
 * Application component for managing images.
 *
 * Methods accessible through the 'ComponentBehavior' class:
 * @method createPathAlias($alias, $path)
 * @method import($alias)
 * @method string publishAssets($path, $forceCopy = false)
 * @method void registerCssFile($url, $media = '')
 * @method void registerScriptFile($url, $position = null)
 * @method string resolveScriptVersion($filename, $minified = false)
 * @method CClientScript getClientScript()
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
     *     'filters' => array(
     *        array('thumbnail', 'width' => 160, 'height' => 90),
     *     ),
     *   ),
     * ),
     */
    public $presets = array();

    /**
     * @var array the placeholder configurations (name => filename).
     */
    public $holders = array();

    /**
     * @var string the name of the default placeholder.
     */
    public $defaultHolder;

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
     * @var string the name of the directory with the placeholder images.
     */
    public $holderDir = 'holder';

    /**
     * @var string the route for creating a preset image (preset must be predefined).
     */
    public $presetRoute = 'image/preset';

    /**
     * @var string the route for creating a placeholder image.
     */
    public $holderRoute = 'image/holder';

    /**
     * @var boolean whether to enable client-side placeholders.
     */
    public $enableClientHolder = true;

    /**
     * @var string the placeholder text for holder.js.
     */
    public $clientHolderText = 'No image';

    /**
     * @var string the name of the image model class.
     */
    public $modelClass = 'Image';

    /**
     * @var string the component id for the file manager.
     */
    public $fileManagerID = 'fileManager';

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
        $this->createPathAlias('imageManager', dirname(__DIR__));
        if ($this->enableClientHolder) {
            $this->registerAssets();
        }
        $this->initPresets();
    }

    /**
     * Registers the assets.
     */
    protected function registerAssets()
    {
        $assetsUrl = $this->publishAssets('assets');
        $cs = $this->getClientScript();
        if ($cs !== false) {
            $cs->registerScriptFile($assetsUrl . '/js/holder.js', CClientScript::POS_END);
        }
    }

    /**
     * Initializes the image presets if applicable.
     */
    protected function initPresets()
    {
        $this->_presets = array();
        if (!empty($this->presets)) {
            foreach ($this->presets as $name => $config) {
                $config['name'] = $name;
                $preset = $this->createPreset($config);
                $this->_presets[$name] = $preset;
            }
        }
    }

    /**
     * Creates an image preset from the given configuration.
     * @param array $config the configuration.
     * @return ImagePreset the object.
     */
    public function createPreset($config)
    {
        return ImagePreset::create($config);
    }

    /**
     * Returns the path to an image preset.
     * @param Image $model the model instance.
     * @param ImagePreset $preset the image preset.
     * @return string the path.
     */
    public function createImagePresetPath($model, $preset)
    {
        return $preset->resolveCachePath() . $model->resolveNormalizedPath();
    }

    /**
     * Returns the url to a image preset.
     * @param Image $model the model instance.
     * @param ImagePreset $preset
     * @return string the url.
     */
    public function createImagePresetUrl($model, $preset)
    {
        return $preset->resolveCacheUrl() . $model->resolveNormalizedPath();
    }

    /**
     * Creates the HTML attributes for rendering a specific image preset.
     * @param string $name the preset name.
     * @param Image $model the model instance.
     * @param string $holder the placeholder name.
     * @return string the url.
     */
    public function createPresetOptions($name, $model = null, $holder = null)
    {
        $options = array();
        $preset = $this->loadPreset($name);
        $options['width'] = $preset->getWidth();
        $options['height'] = $preset->getHeight();
        list($imageUrl, $holderUrl) = $this->resolveImagePresetUrls($model, $preset, $holder);
        $options['src'] = $imageUrl !== null ? $imageUrl : $holderUrl;
        if ($holderUrl === null && $this->enableClientHolder) {
            $options['data-src'] = $this->createClientHolderUrl($preset->getWidth(), $preset->getHeight());
        }
        return $options;
    }

    /**
     * Returns the image or holder url.
     * @param Image $model the model instance.
     * @param ImagePreset $preset the image preset.
     * @param string $holder the placeholder name.
     * @return array an array with the image (index 0) and holder (index 1) urls.
     */
    public function resolveImagePresetUrls($model, $preset, $holder = null)
    {
        if ($holder === null && isset($this->defaultHolder)) {
            $holder = $this->defaultHolder;
        }
        return array(
            $model !== null ? $this->createImagePresetUrl($model, $preset) : null,
            $holder !== null ? $this->createHolderUrl($holder, $preset) : null
        );
    }

    /**
     * Returns the url for a specific placeholder image preset.
     * @param string $name the placeholder name.
     * @param ImagePreset $preset the preset.
     * @param boolean $absolute whether the url should be absolute (defaults to true).
     * @return string the url.
     */
    public function createHolderUrl($name, $preset, $absolute = true)
    {
        return $preset->resolveCacheUrl($absolute) . '/' . $this->holderDir . '/' . $name . '.png';
    }

    /**
     * Returns the holder.js url with the given dimensions.
     * @param integer $width the image width.
     * @param integer $height the image height.
     * @return string the url.
     */
    public function createClientHolderUrl($width, $height)
    {
        return 'holder.js/' . $width . 'x' . $height . '/text:' . $this->clientHolderText;
    }

    /**
     * Creates a preset image for the image model with the given id.
     * @param ImagePreset $preset the image preset instance.
     * @param Image $model the model.
     * @param string $format the image file format.
     * @return ImageInterface the image.
     */
    public function createPresetImage($preset, $model, $format)
    {
        $rawPath   = $model->file->resolvePath();
        $image     = $this->openImageWithPreset($rawPath, $preset);
        $filePath  = $model->resolveNormalizedPath();
        $filePath  = substr($filePath, 0, strrpos($filePath, '/'));
        $filename  = $model->file->resolveFilename($format);
        return $preset->saveCachedImage($image, $filePath, $filename, array('format' => $format));
    }

    /**
     * Creates a preset image for a specific placeholder image.
     * @param string $name the preset name.
     * @param string $holder the placeholder name.
     * @return ImageInterface the image.
     */
    public function createPresetHolder($name, $holder)
    {
        $preset    = $this->loadPreset($name);
        $path      = $this->resolveHolderImagePath($holder);
        $image     = $this->openImageWithPreset($path, $preset);
        $filename  = $this->resolveHolderFilename($holder);
        return $preset->saveCachedImage($image, $this->holderDir, $filename);
    }

    /**
     * Returns the path to a specific placeholder image.
     * @param string $name the placeholder name.
     * @return string the path.
     * @throws CException if the placeholder is not defined.
     */
    protected function resolveHolderImagePath($name)
    {
        if (!isset($this->holders[$name])) {
            throw new CException(sprintf('Holder "%s" is not defined.', $name));
        }
        return $this->resolveHolderPath(true) . '/' . $this->holders[$name];
    }

    /**
     * Returns the filename for the given placeholder.
     * @param string $name the placeholder name.
     * @return string the file name.
     */
    protected function resolveHolderFilename($name)
    {
        return $name . '.' . Image::FORMAT_PNG;
    }

    /**
     * Loads a specific preset.
     * @param string $name the preset name.
     * @return ImagePreset the preset.
     * @throws CException if the preset is not found.
     */
    public function loadPreset($name)
    {
        if (!isset($this->_presets[$name])) {
            throw new CException(sprintf('Preset "%s" is not defined.', $name));
        }
        return $this->_presets[$name];
    }

    /**
     * Normalizes the given path by removing the raw path.
     * @param string $path the path to normalize.
     * @return string the path.
     */
    public function normalizePath($path)
    {
        return str_replace($this->resolveRawPath(), '', $path);
    }

    /**
     * Creates an image model.
     * @param string $scenario the scenario name.
     * @return Image the image model.
     * @throws CException if the model class does not extend the "Image" class.
     */
    public function createModel($scenario = 'insert')
    {
        /* @var Image $model */
        $model = new $this->modelClass($scenario);
        if (!$model instanceof Image) {
            throw new CException(sprintf('Model class "%s" must extend "Image".', $this->modelClass));
        }
        return $model;
    }

    /**
     * Saves an image file on the hard drive and in the database.
     * @param FileResource $resource the file resource.
     * @param string $name the file name.
     * @param string $path the file path.
     * @param string $scenario the scenario name.
     * @return Image the image model.
     * @throws CException if saving the image model is not successful.
     */
    public function saveModel(FileResource $resource, $name = null, $path = null, $scenario = 'insert')
    {
        $model         = $this->createModel($scenario);
        $fileManager   = $this->getFileManager();
        $path          = $this->imageDir . '/' . $this->rawDir . '/' . $path;
        $file          = $fileManager->saveModel($resource, $name, $path);
        $savePath      = $file->resolvePath();
        $image         = $this->openImage($savePath);
        $model->fileId = $file->id;
        $size          = $image->getSize();
        $model->width  = $size->getWidth();
        $model->height = $size->getHeight();
        if (!$model->save()) {
            throw new CException('Failed to save the image model.');
        }
        return $model;
    }

    /**
     * Loads an image model.
     * @param integer $id the model id.
     * @param mixed $with related models that should be eager-loaded.
     * @return Image the model.
     */
    public function loadModel($id, $with = array())
    {
        return CActiveRecord::model($this->modelClass)->with($with)->findByPk($id);
    }

    /**
     * Loads an image model by its file id.
     * @param integer $fileId the file id.
     * @param mixed $with related models that should be eager-loaded.
     * @return Image the model.
     */
    public function loadModelByFileId($fileId, $with = array())
    {
        return CActiveRecord::model($this->modelClass)->with($with)->findByAttributes(array('fileId' => $fileId));
    }

    /**
     * Deletes an image model.
     * @param integer $id the model id.
     * @throws CException
     * @return boolean the result.
     */
    public function deleteModel($id)
    {
        if (($model = $this->loadModel($id)) === null) {
            throw new CException(sprintf('Failed to locate image model with id "%d".', $id));
        }
        $fileId = $model->fileId;
        $result = $model->delete();
        $this->getFileManager()->deleteModel($fileId);
        return $result;
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
     * Opens an image and applies to filters in the given preset.
     * @param string $path the image path.
     * @param ImagePreset $preset the preset name.
     * @return ImageInterface the image.
     */
    public function openImageWithPreset($path, $preset)
    {
        return $preset->applyFilters($this->openImage($path));
    }

    /**
     * Returns the path for a specific directory.
     * @param string $name the directory name.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    protected function resolveDirectoryPath($name, $absolute = false)
    {
        return $this->getBasePath($absolute) . '/' . $name;
    }

    /**
     * Returns the url for a specific directory.
     * @param string $name the directory name.
     * @param boolean $absolute whether the url should be absolute.
     * @return string the url.
     */
    protected function resolveDirectoryUrl($name, $absolute = false)
    {
        return $this->getBaseUrl($absolute) . '/' . $name;
    }

    /**
     * Returns the path to the raw images.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function resolveRawPath($absolute = false)
    {
        return $this->resolveDirectoryPath($this->rawDir, $absolute);
    }

    /**
     * Returns the path to the cached images.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function resolveCachePath($absolute = false)
    {
        return $this->resolveDirectoryPath($this->cacheDir, $absolute);
    }

    /**
     * Returns the url to the cached images.
     * @param boolean $absolute whether the url should be absolute.
     * @return string the url.
     */
    public function resolveCacheUrl($absolute = false)
    {
        return $this->resolveDirectoryUrl($this->cacheDir, $absolute);
    }

    /**
     * Returns the path to the placeholder images.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function resolveHolderPath($absolute = false)
    {
        return $this->resolveDirectoryPath($this->holderDir, $absolute);
    }

    /**
     * Returns the url to the placeholder images.
     * @param boolean $absolute whether the url should be absolute.
     * @return string the url.
     */
    public function resolveHolderUrl($absolute = false)
    {
        return $this->resolveDirectoryUrl($this->holderDir, $absolute);
    }

    /**
     * Returns the path to the images folder.
     * @param boolean $absolute whether to return an absolute path.
     * @return string the path.
     */
    public function getBasePath($absolute = false)
    {
        return $this->getFileManager()->getBasePath($absolute) . '/' . $this->imageDir;
    }

    /**
     * Returns the url to the images folder.
     * @param boolean $absolute whether to return an absolute url.
     * @return string the url.
     */
    public function getBaseUrl($absolute = false)
    {
        return $this->getFileManager()->getBaseUrl($absolute) . '/' . $this->imageDir;
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
                throw new CException(sprintf('Driver "%s" not supported.', $driver));
        }
    }

    /**
     * Returns the file manager component.
     * @return FileManager the component.
     * @throws CException if the component is not found.
     */
    public function getFileManager()
    {
        if (!Yii::app()->hasComponent($this->fileManagerID)) {
            throw new CException(sprintf(
                __CLASS__ . '.fileManagerID "%s" is invalid, please make sure that exists.',
                $this->fileManagerID
            ));
        }
        return Yii::app()->getComponent($this->fileManagerID);
    }
}