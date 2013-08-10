<?php
/**
 * ImagineFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\FilterInterface;
use Imagine\Image\Box;
use Imagine\Image\BoxInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use Imagine\Image\PointInterface;

/**
 * Base class for the all Imagine filters.
 */
abstract class ImagineFilter extends CComponent
{
    // List of supported filters.
    const FILTER_UNDEFINED = 'undefined';
    const FILTER_POINT     = 'point';
    const FILTER_BOX       = 'box';
    const FILTER_TRIANGLE  = 'triangle';
    const FILTER_HERMITE   = 'hermite';
    const FILTER_HANNING   = 'hanning';
    const FILTER_HAMMING   = 'hamming';
    const FILTER_BLACKMAN  = 'blackman';
    const FILTER_GAUSSIAN  = 'gaussian';
    const FILTER_QUADRATIC = 'quadratic';
    const FILTER_CUBIC     = 'cubic';
    const FILTER_CATROM    = 'catrom';
    const FILTER_MITCHELL  = 'mitchell';
    const FILTER_LANCZOS   = 'lanczos';
    const FILTER_BESSEL    = 'bessel';
    const FILTER_SINC      = 'sinc';

    // Map of the supported imagine filters.
    public static $builtInFilters = array(
        'applyMask'        => 'ApplyMaskFilter',
        'border'           => 'BorderFilter',
        'canvas'           => 'CanvasFilter',
        'copy'             => 'CopyFilter',
        'crop'             => 'CropFilter',
        'fill'             => 'FillFilter',
        'flipHorizontally' => 'FlipHorizontallyFilter',
        'flipVertically'   => 'FlipVerticallyFilter',
        'grayscale'        => 'GrayscaleFilter',
        'paste'            => 'PasteFilter',
        'relativeResize'   => 'RelativeResizeFilter',
        'resize'           => 'ResizeFilter',
        'rotate'           => 'RotateFilter',
        'save'             => 'SaveFilter',
        'show'             => 'ShowFilter',
        'strip'            => 'StripFilter',
        'thumbnail'        => 'ThumbnailFilter',
    );

    /**
     * @var FilterInterface
     */
    protected $_filter;

    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    abstract public function load();

    /**
     * Applies the filter to the given image interface.
     * @param ImageInterface $image the image interface.
     * @return ImageInterface
     */
    public function apply(ImageInterface $image)
    {
        return $this->getFilter()->apply($image);
    }

    /**
     * Creates a filter of a specific type.
     * @param string $name the filter type.
     * @param array $params filter options.
     * @return ImagineFilter the filter.
     */
    public static function create($name, $params = array())
    {
        if (isset(self::$builtInFilters[$name])) {
            $className = Yii::import(self::$builtInFilters[$name], true);
        } else {
            $className = Yii::import($name, true);
        }
        /* @var ImagineFilter $filter */
        $filter = new $className;
        foreach ($params as $key => $value) {
            $filter->$key = $value;
        }
        return $filter;
    }

    /**
     * Creates a Imagine box object.
     * @param integer $width the width in pixels.
     * @param integer $height the height in pixels.
     * @return BoxInterface the object.
     */
    protected function createBox($width, $height)
    {
        return new Box($width, $height);
    }

    /**
     * Creates an Imagine color object.
     * @param mixed $color the color.
     * @return Imagine\Image\Palette\RGB the object.
     */
    protected function createColor($color)
    {
        if (is_array($color)) {
            list($color, $alpha) = $color;
        }
        if (!isset($color)) {
            $color = '#ffffff';
        }
        if (!isset($alpha)) {
            $alpha = 0;
        }
        $palette = new Imagine\Image\Palette\RGB();
        return $palette->color($color, $alpha);
    }

    /**
     * Creates a Imagine point object.
     * @param integer $x the position on the x-axis.
     * @param integer $y the position on the y-axis.
     * @return PointInterface the object.
     */
    protected function createPoint($x, $y)
    {
        return new Point($x, $y);
    }

    /**
     * Returns the associated filter.
     * @return FilterInterface
     */
    protected function getFilter()
    {
        if (isset($this->_filter)) {
            return $this->_filter;
        } else {
            return $this->_filter = $this->load();
        }
    }
}