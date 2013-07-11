<?php
/**
 * GrayscaleFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Advanced\Grayscale;
use Imagine\Filter\FilterInterface;

/**
 * Loader for the Imagine grayscale filter.
 */
class GrayscaleFilter extends ImagineFilter
{
    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    public function load()
    {
        return new Grayscale();
    }
}