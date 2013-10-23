<?php
/**
 * UploadedFile class file.
 * @author Christoffer Lindqvist <christoffer.lindqvist@nordsoftware.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.resources
 */

use \Imagine\Image\ImageInterface;

/**
 * File resource for an Imagine image instance.
 */
class ImagineFile extends FileResource
{
    /**
     * @var ImageInterface the imagine image instance.
     */
    protected $image;

    /**
     * @var string the file name.
     */
    protected $name;

    /**
     * @var string the file extension.
     */
    protected $extension;

    /**
     * @var array file save options.
     */
    protected $saveOptions = array();

    /**
     * Creates a new file resource.
     * @param string $name the desired file name.
     * @param string $extension the file extension.
     * @param ImageInterface $image the imagine image instance.
     */
    public function __construct($name, $extension, ImageInterface $image)
    {
        $this->name = $name;
        $this->extension = $extension;
        $this->image = $image;
    }

    /**
     * Returns the file name for this file.
     * @return string the filename.
     */
    public function resolveFilename()
    {
        return $this->name . '.' . $this->extension;
    }

    /**
     * Returns the file name.
     * @return string the filename.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the file extension.
     * @return string the extension.
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Returns the file mime type.
     * @return string the file mime type.
     * @throws CException if the mime type cannot be determined.
     */
    public function getMimeType()
    {
        if (($mimeType = CFileHelper::getMimeTypeByExtension($this->resolveFilename())) !== null) {
            return $mimeType;
        }
        throw new CException(sprintf('Failed to determine mime type for file "%s"', $this->resolveFilename()));
    }

    /**
     * Returns the files size in bytes.
     * @return int the file size.
     */
    public function getSize()
    {
        // as we don't have a physical file we need to perform some magic to get the size.
        ob_start();
        $this->image->show($this->extension);
        $size = ob_get_length();
        ob_end_clean();
        return $size;
    }

    /**
     * Setter for the file save options.
     * @param array $options save options.
     */
    public function setSaveOptions($options)
    {
        $this->saveOptions = $options;
    }

    /**
     * Saves the file to the specified path.
     * @param string $path the path where to save the file.
     * @return bool if the file was successfully saved.
     */
    public function saveAs($path)
    {
        $this->image->save($path, $this->saveOptions);
        return true;
    }
}