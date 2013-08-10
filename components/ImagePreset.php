<?php
/**
 * ImagePreset class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.components
 */

use Imagine\Image\ImageInterface;

/**
 * Component that represents a single image preset.
 */
class ImagePreset extends CComponent
{
    /**
     * @var string the preset name.
     */
    public $name;
    /**
     * @var string the image file format.
     */
    public $format;
    /**
     * @var boolean whether to allow caching of the preset images.
     */
    public $allowCache = true;

    /** @var ImagineFilter[] */
    protected $_filters;
    /** @var ImageManager */
    protected $_manager;

    /**
     * Initializes the preset.
     */
    public function init()
    {
        $this->_filters = array();
    }

    /**
     * Returns the path to the cached version of the image preset.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function resolveCachePath($absolute = true)
    {
        return implode('/', array(
            $this->_manager->resolveCachePath($absolute),
            $this->name,
            $this->calculateCacheChecksum(),
        ));
    }

    /**
     * Returns the url to the cached version of the image preset.
     * @param boolean $absolute whether the url should be absolute.
     * @return string the url.
     */
    public function resolveCacheUrl($absolute = true)
    {
        return implode('/', array(
            $this->_manager->resolveCacheUrl($absolute),
            $this->name,
            $this->calculateCacheChecksum(),
        ));
    }

    /**
     * Calculates the checksum from this preset.
     * Override this method to change how to checksum is calculated.
     * @return string the checksum.
     */
    protected function calculateCacheChecksum()
    {
        return sprintf('%x', crc32(CJSON::encode($this)));
    }

    /**
     * Adds a filter to this preset.
     * @param ImagineFilter $filter the filter.
     */
    public function addFilter($filter)
    {
        $this->_filters[] = $filter;
    }

    /**
     * Applies the filters in this preset to the given image.
     * @param ImageInterface $image the image.
     * @return ImageInterface the image.
     */
    public function applyFilters(ImageInterface $image)
    {
        foreach ($this->_filters as $filter) {
            $image = $filter->apply($image);
        }
        return $image;
    }

    /**
     * Sets the image manager application component.
     * @param ImageManager $manager the component.
     */
    public function setManager($manager)
    {
        $this->_manager = $manager;
    }

    /**
     * Creates a preset with the given configuration.
     * @param array $config the configuration.
     * @return ImagePreset the object.
     */
    public static function create($config)
    {
        if (!isset($config['class'])) {
            $config['class'] = 'imageManager.components.ImagePreset';
        }
        $filters = array();
        if (isset($config['filters'])) {
            foreach ($config['filters'] as $filter) {
                if (is_array($filter)) {
                    $filter = ImagineFilter::create($filter[0], array_slice($filter, 1));
                }
                if (is_object($filter)) {
                    $filters[] = $filter;
                }
            }
            unset($config['filters']);
        }
        /** @var ImagePreset $preset */
        $preset = Yii::createComponent($config);
        $preset->init();
        foreach ($filters as $filter) {
            $preset->addFilter($filter);
        }
        return $preset;
    }
}