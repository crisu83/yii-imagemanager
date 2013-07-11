<?php
/**
 * FillFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\Fill;
use Imagine\Filter\FilterInterface;
use Imagine\Image\Fill\FillInterface;

/**
 * Loader for the Imagine fill filter.
 */
class FillFilter extends ImagineFilter
{
    /**
     * @var FillInterface the fill object.
     */
    public $fill;

    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    public function load()
    {
        return new Fill($this->fill);
    }
}