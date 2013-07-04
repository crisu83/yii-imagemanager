<?php
/**
 * ImageManager class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package crisu83.yii-imagemanager.components
 */

// Let Yii's autoloader know where to find the Imagine classes.
Yii::setPathOfAlias('Imagine', Yii::getPathOfAlias('vendor.imagine.imagine.lib.Imagine'));

// Import the extension component behavior.
Yii::import('vendor.crisu83.yii-extension.behaviors.ComponentBehavior');

/**
 * Application component for managing images.
 */
class ImageManager extends CApplicationComponent
{
	const DRIVER_GD = 'gd';
	const DRIVER_IMAGICK = 'imagick';

	/**
	 * @var string
	 */
	public $driver = self::DRIVER_GD;
	/**
	 * @var array
	 */
	public $presets = array();
	/**
	 * @var string
	 */
	public $imageDir = 'images';
	/**
	 * @var string
	 */
	public $originalDir = 'originals';
	/**
	 * @var string
	 */
	public $presetDir = 'presets';
	/**
	 * @var string
	 */
	public $fileManagerID = 'fileManager';

	private $_basePath;
	private $_fileManager;
	private $_factory;

	/**
	 * Initializes the component.
	 */
	public function init()
	{
		parent::init();
		$this->attachBehavior('ext', new ComponentBehavior);
		$this->createPathAlias('imageManager', __DIR__ . DIRECTORY_SEPARATOR . '..');
		$this->import('models.*');
	}

	public function save($file, $name = null, $path = null)
	{
		$fileManager = $this->getFileManager();
		$path = $this->getOriginalPath() . $path;
		$file = $fileManager->save($file, $name, $path);
		$model = new Image;
		$image = $this->openFile($fileManager->resolveFilePath($file));
		$model->fileId = $file->id;
		$size = $image->getSize();
		$model->width = $size->getWidth();
		$model->height = $size->getHeight();
		if ($model->save() === false)
			throw new CException('Failed to save image. Database record could not be saved.');
		return $model;
	}

	public function openFile($path)
	{
		return $this->getFactory()->open($path);
	}

	/**
	 * @param boolean $absolute
	 * @return string
	 */
	public function getOriginalPath($absolute = false)
	{
		return $this->getImagePath($absolute) . $this->originalDir . '/';
	}

	/**
	 * @param boolean $absolute
	 * @return string
	 */
	public function getPresetPath($absolute = false)
	{
		return $this->getImagePath($absolute) . $this->presetDir . '/';
	}

	/**
	 * @param boolean $absolute
	 * @return string
	 */
	protected function getImagePath($absolute = false)
	{
		$path = $this->imageDir;
		if ($absolute)
			$path = $this->getBasePath() . $path;
		return $path . '/';
	}

	/**
	 * @return string
	 */
	protected function getBasePath()
	{
		if (isset($this->_basePath))
			return $this->_basePath;
		else
		{
			$filePath = $this->getFileManager()->getBasePath();
			$basePath = $filePath . '/' . $this->imageDir . '/';
			return $this->_basePath = $basePath;
		}
	}

	/**
	 * @return Imagine\Image\ImagineInterface
	 */
	public function getFactory()
	{
		if (isset($this->_factory))
			return $this->_factory;
		else
			return $this->_factory = $this->createFactory($this->driver);
	}

	/**
	 * @param $driver
	 * @return Imagine\Image\ImagineInterface
	 * @throws CException
	 */
	protected function createFactory($driver)
	{
		switch ($driver)
		{
			case self::DRIVER_GD:
				return new Imagine\Gd\Imagine;
			case self::DRIVER_IMAGICK:
				return new Imagine\Imagick\Imagine;
			default:
				throw new CException('Failed to create image factory. Driver not found.');
		}
	}

	/**
	 * @return FileManager
	 * @throws CException
	 */
	protected function getFileManager()
	{
		if (isset($this->_fileManager))
			return $this->_fileManager;
		else
		{
			if (!Yii::app()->hasComponent($this->fileManagerID))
				throw new CException('Failed to get file manager. Application component could not be found.');
			return $this->_fileManager = Yii::app()->getComponent($this->fileManagerID);
		}
	}
}
