<?php
/**
 * ThumbnailFilter class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Filter\Basic\Thumbnail;
use Imagine\Filter\FilterInterface;

/**
 * Loader for the Imagine thumbnail filter.
 */
class ThumbnailFilter extends ImagineFilter
{
    const MODE_INSET = 'inset';
    const MODE_OUTBOUND = 'outbound';

    /**
     * @var integer the thumbnail width.
     */
    public $width;
    /**
     * @var integer the thumbnail height.
     */
    public $height;
    /**
     * @var string the thumbnail mode. Valid values are 'inset' and 'outbound' (defaults to 'inset').
     */
    public $mode = self::MODE_INSET;

    /**
     * Loads the filter.
     * @return FilterInterface
     */
    public function load()
    {
        $size = $this->createBox($this->width, $this->height);
        return new Thumbnail($size, $this->mode);
    }
}