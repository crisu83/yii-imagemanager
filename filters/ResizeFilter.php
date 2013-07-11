<?php
/**
 * ResizeFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\Resize;
use Imagine\Filter\FilterInterface;

/**
 * Loader for the Imagine resize filter.
 */
class ResizeFilter extends ImagineFilter
{
    /**
     * @var integer the resize width.
     */
    public $width;
    /**
     * @var integer the resize height.
     */
    public $height;
    /**
     * @var string the filter to use.
     */
    public $filter = ImagineFilter::FILTER_UNDEFINED;

    /**
     * Loads the filter.
     * @return FilterInterface
     */
    public function load()
    {
        $size = $this->createBox($this->width, $this->height);
        return new Resize($size, $this->filter);
    }
}