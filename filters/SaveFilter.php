<?php
/**
 * SaveFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\Save;
use Imagine\Filter\FilterInterface;

/**
 * Loader for the Imagine save filter.
 */
class SaveFilter extends ImagineFilter
{
    /**
     * @var string the save path.
     */
    public $path;
    /**
     * @var array the save options.
     */
    public $options;

    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    public function load()
    {
        return new Save($this->path, $this->options);
    }
}