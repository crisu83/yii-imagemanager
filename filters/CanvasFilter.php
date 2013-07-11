<?php
/**
 * CanvasFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Advanced\Canvas;
use Imagine\Filter\FilterInterface;
use Imagine\Image\ImagineInterface;

/**
 * Loader for the Imagine canvas filter.
 */
class CanvasFilter extends ImagineFilter
{
    /**
     * @var integer the canvas position on the x-axis.
     */
    public $x;
    /**
     * @var integer the canvas position on the y-axis.
     */
    public $y;
    /**
     * @var integer the canvas width.
     */
    public $width;
    /**
     * @var integer the canvas height.
     */
    public $height;
    /**
     * @var string the canvas background.
     */
    public $background;
    /**
     * @var ImagineInterface the image factory.
     */
    public $factory;

    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    public function load()
    {
        $placement  = $this->createPoint($this->x, $this->y);
        $size       = $this->createBox($this->width, $this->height);
        $background = $this->createColor($this->background);
        return new Canvas($this->factory, $size, $placement, $background);
    }
}