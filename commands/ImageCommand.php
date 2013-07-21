<?php
/**
 * ImageCommand class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.commands
 */

/**
 * Console command for image actions.
 */
class ImageCommand extends CConsoleCommand
{
    /**
     * @var string the image manager component id.
     */
    public $componentID = 'imageManager';

    /** @var ImageManager */
    private $_imageManager;

    /**
     * Provides the command description.
     * @return string the command description.
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic image <action> <options>

DESCRIPTION
  Console command for performing shell tasks associated with the yii-imagemanager extension.

EXAMPLES
 * yiic image createAccessFile --baseUrl="/" --filename="/var/www/files/images/.htaccess"
   Creates an .htaccess file in the "/var/www/files/images" directory with "/" as its rewrite base url.
EOD;
    }

    /**
     * Creates an access file for rewriting missing images to the image controller.
     * @param string $baseUrl the rewrite base url.
     * @param string $filename the full path for the access file.
     * @throws CException if the access file cannot be created.
     */
    public function actionCreateAccessFile($baseUrl, $filename)
    {
        $imageManager = $this->getImageManager();
        $cacheDir = $imageManager->cacheDir;
        $createPresetRoute = $imageManager->createPresetRoute;
        $data = <<<EOD
<IfModule mod_rewrite.c>

        RewriteEngine on
        RewriteBase {$baseUrl}

        # If the requested file or directory does not exist...
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d

        # ...and if the source URL points to an image, we redirect to the create image URL.
        RewriteRule {$cacheDir}/([^/]+)/.*[^\d](\d+)\.(?:gif|jpg|jpeg|png)$ {$createPresetRoute}?id=$2&name=$1 [L,R,QSA]

</IfModule>
EOD;
        if ((file_put_contents($filename, $data)) === false) {
            throw new CException(sprintf('Failed to create access file. Could not write file to path "%s".', $filename));
        }
        echo "File {$filename} created.\n";
    }

    /**
     * Returns the image manager component.
     * @return ImageManager the component.
     * @throws CException if the component is not found.
     */
    protected function getImageManager()
    {
        if ($this->_imageManager !== null) {
            return $this->_imageManager;
        } else {
            if (($imageManager = Yii::app()->getComponent($this->componentID)) == null) {
                throw new CException('Failed to get image manager. Component not found.');
            }
            return $this->_imageManager = $imageManager;
        }
    }
}