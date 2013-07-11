<?php
/**
 * ApplyMaskFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\ApplyMask;
use Imagine\Filter\FilterInterface;
use Imagine\Image\ImageInterface;

/**
 * Loader for the Imagine apply mask filter.
 */
class ApplyMaskFilter extends ImagineFilter
{
    /**
     * @var ImageInterface the mask image.
     */
    public $mask;

    /**
     * Loads the filter.
     * @return FilterInterface
     */
    public function load()
    {
        return new ApplyMask($this->mask);
    }
}