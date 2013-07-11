<?php
/**
 * ImagineFilterChain class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.filters
 */

use Imagine\Image\ImageInterface;

/**
 * Filter chain for Imagine filters.
 */
class ImagineFilterChain extends CList
{
    /**
     * Creates the filter chain.
     * @param ImagineFilter[] $filters the filters.
     * @return ImagineFilterChain
     */
    public static function create($filters)
    {
        $chain = new ImagineFilterChain();
        foreach ($filters as $filter) {
            // todo: add some error handling.
            if (is_array($filter)) {
                $filter = ImagineFilter::createFilter($filter[0], array_slice($filter, 1));
            }
            if (is_object($filter)) {
                $chain->add($filter);
            }
        }
        return $chain;
    }

    /**
     * Applies this filter chain on the given image.
     * @param ImageInterface $image the image.
     * @return ImageInterface
     */
    public function apply(ImageInterface $image)
    {
        /** @var ImagineFilter $filter */
        foreach ($this as $filter) {
            $image = $filter->apply($image);
        }
        return $image;
    }
}