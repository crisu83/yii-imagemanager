<?php
/**
 * BorderFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Advanced\Border;
use Imagine\Filter\FilterInterface;

/**
 * Loader for the Imagine border filter.
 */
class BorderFilter extends ImagineFilter
{
    /**
     * @var mixed the border color.
     */
    public $color;
    /**
     * @var integer the border width (defaults to 1).
     */
    public $width = 1;
    /**
     * @var integer the border height (defaults to 1).
     */
    public $height = 1;

    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    public function load()
    {
        $color = $this->createColor($this->color);
        return new Border($color, $this->width, $this->height);
    }
}