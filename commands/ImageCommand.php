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
 * yiic image createAccessFile --path="/var/www/files/images" --baseUrl="/" --filename=".htaccess"
   Creates an .htaccess file in the "/var/www/files/images" directory with "/" as its rewrite base url.
EOD;
    }

    /**
     * Creates an access file for rewriting missing images to the image controller.
     * @param string $path the path to the directory where the access file should be created.
     * @param string $baseUrl the rewrite base url (defaults to '/')..
     * @param string $filename the filename for the access file (defaults to '.htaccess').
     * @throws CException if the access file cannot be created.
     */
    public function actionCreateAccessFile($path, $baseUrl = '/', $filename = '.htaccess')
    {
        $imageManager = $this->getImageManager();
        $cacheDir = $imageManager->cacheDir;
        $holderDir = $imageManager->holderDir;
        $holderRoute = $imageManager->holderRoute;
        $presetRoute = $imageManager->presetRoute;
        $baseUrl = '/' . ltrim($baseUrl, '/');
        $data = <<<EOD
<IfModule mod_rewrite.c>

    RewriteEngine on
    RewriteBase {$baseUrl}

    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d

    # If the requested file exists; then skip the following two rewrite rules.
    RewriteRule .* - [S=2]

    # Rewrite rule for missing placeholder images.
    RewriteRule {$cacheDir}/([^/]+)/.*/{$holderDir}/(.*)\.(gif|jpg|jpeg|png)$ {$holderRoute}?name=$2&preset=$1&format=$3 [L,R=302,QSA]

    # Rewrite rule for missing images.
    RewriteRule {$cacheDir}/([^/]+)/.*[^\d](\d+)\.(gif|jpg|jpeg|png)$ {$presetRoute}?name=$1&fileId=$2&format=$3 [L,R=302,QSA]

</IfModule>
EOD;
        $this->ensureDirectory($path);
        $filePath = $path . '/' . $filename;
        if ((file_put_contents($filePath, $data)) === false) {
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