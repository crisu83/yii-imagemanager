<?php
/**
 * PasteFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\Paste;
use Imagine\Filter\FilterInterface;
use Imagine\Image\ImageInterface;

/**
 * Loader for the Imagine paste filter.
 */
class PasteFilter extends ImagineFilter
{
    /**
     * @var ImageInterface the image to paste.
     */
    public $image;
    /**
     * @var integer the start coordinate on the x-axis.
     */
    public $x;
    /**
     * @var integer the start coordinate on the y-axis.
     */
    public $y;

    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    public function load()
    {
        $start = $this->createPoint($this->x, $this->y);
        return new Paste($this->image, $start);
    }
}
