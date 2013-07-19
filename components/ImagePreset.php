<?php
/**
 * ImagePreset class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.components
 */

/**
 * Component that represents a single image preset.
 */
class ImagePreset extends CComponent
{
    // todo: consider adding support for settings HTTP headers when rendering preset images.
    /**
     * @var string the preset name.
     */
    public $name;
    /**
     * @var boolean whether to allow caching of the preset images.
     */
    public $allowCache = true;
    /**
     * @var array the filter configurations.
     */
    public $filters = array();

    /** @var ImagineFilterChain */
    protected $_filterChain;
    /** @var ImageManager */
    protected $_manager;

    /**
     * Initializes the preset.
     */
    public function init()
    {
        if (!empty($this->filters)) {
            $this->_filterChain = ImagineFilterChain::create($this->filters);
        }
    }

    /**
     * Returns the path to the cached version of the image preset.
     * @param boolean $absolute whether the path should be absolute.
     * @return string the path.
     */
    public function resolveCachePath($absolute = true)
    {
        $checksum = $this->calculateCacheChecksum();
        return $this->_manager->resolveCachePath($absolute) . $this->name . '/' . $checksum . '/';
    }

    /**
     * Returns the url to the cached version of the image preset.
     * @param boolean $absolute whether the url should be absolute.
     * @return string the url.
     */
    public function resolveCacheUrl($absolute = true)
    {
        $checksum = $this->calculateCacheChecksum();
        return $this->_manager->resolveCacheUrl($absolute) . $this->name . '/' . $checksum . '/';
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
     * Returns the filter chain component for this preset.
     * @return ImagineFilterChain the component.
     */
    public function getFilterChain()
    {
        return $this->_filterChain;
    }

    /**
     * Sets the image manager application component.
     * @param ImageManager $manager the component.
     */
    public function setManager($manager)
    {
        $this->_manager = $manager;
    }
}