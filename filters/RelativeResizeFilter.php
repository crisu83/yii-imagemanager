<?php
/**
 * RelativeResizeFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Advanced\RelativeResize;
use Imagine\Filter\FilterInterface;

/**
 * Loader for the Imagine relative resize filter.
 */
class RelativeResizeFilter extends ImagineFilter
{
    // List of supported resize methods.
    const METHOD_HEIGHTEN = 'heighten';
    const METHOD_INCREASE = 'increase';
    const METHOD_SCALE = 'scale';
    const METHOD_WIDEN = 'widen';

    /**
     * @var string the resize method.
     */
    public $method;
    /**
     * @var mixed the parameters for the resize method.
     */
    public $params;

    /**
     * Loads the actual filter.
     * @return FilterInterface
     */
    public function load()
    {
        return new RelativeResize($this->method, $this->params);
    }
}