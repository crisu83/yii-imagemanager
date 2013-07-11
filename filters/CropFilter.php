<?php
/**
 * CropFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\Crop;
use Imagine\Filter\FilterInterface;

/**
 * Loader for the Imagine crop filter.
 */
class CropFilter extends ImagineFilter
{
    /**
     * @var integer the start point on the x-axis.
     */
    public $x = 0;
    /**
     * @var integer the start point on the y-axis.
     */
    public $y = 0;
    /**
     * @var integer the crop width.
     */
    public $width = 0;
    /**
     * @var integer the crop height.
     */
    public $height = 0;

    /**
     * Loads the filter.
     * @return FilterInterface
     */
    public function load()
    {
        $start = $this->createPoint($this->x, $this->y);
        $size  = $this->createBox($this->width, $this->height);
        return new Crop($start, $size);
    }
}