<?php
/**
 * ShowFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\Show;
use Imagine\Filter\FilterInterface;

/**
 * Loader for the Imagine show filter.
 */
class ShowFilter extends ImagineFilter
{
    /**
     * @var string the file format.
     */
    public $format;
    /**
     * @var array the show options.
     */
    public $options;

    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    public function load()
    {
        return new Show($this->format, $this->options);
    }
}