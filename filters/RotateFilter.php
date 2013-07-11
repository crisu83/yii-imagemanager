<?php
/**
 * RotateFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\Rotate;
use Imagine\Filter\FilterInterface;
use Imagine\Image\Color;

/**
 * Loader for the Imagine rotate filter.
 */
class RotateFilter extends ImagineFilter
{
    /**
     * @var integer the rotate angle in degrees.
     */
    public $angle;
    /**
     * @var Color the background color.
     */
    public $background;

    /**
     * Loads the filter.
     * @return FilterInterface
     */
    public function load()
    {
        $color = $this->createColor($this->background);
        return new Rotate($this->angle, $color);
    }
}