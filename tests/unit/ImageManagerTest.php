<?php

use Codeception\Util\Stub;

class ImageManagerTest extends \YiiTest
{
   /**
    * @var \CodeGuy
    */
    protected $codeGuy;

    protected function _before()
    {
        $this->mockApplication(
            array(
                'aliases' => array(
                    'project' => realpath(__DIR__ . '/../..'),
                    'vendor' => 'project.vendor',
                ),
                'components' => array(
                    'fileManager' => array(
                        'class' => 'vendor.crisu83.yii-filemanager.components.FileManager',
                        'baseUrl' => '/test',
                    ),
                    'imageManager' => array(
                        'class' => 'project.components.ImageManager',
                        'enableClientHolder' => false,
                    ),
                ),
                'preload' => array(
                    'fileManager',
                    'imageManager',
                ),
            )
        );
    }

    public function testInit()
    {
        $this->assertNotEquals(false, Yii::getPathOfAlias('imageManager'));
        $this->assertTrue(class_exists('Image'));
    }

    public function testCreatePreset()
    {
        // todo: implement
    }

    public function testCreatePresetUrl()
    {
        // todo: implement
    }

    public function testCreateHolderUrl()
    {
        // todo: implement
    }

    public function testCreatePresetImage()
    {
        // todo: implement
    }

    public function testCreatePresetHolder()
    {
        // todo: implement
    }

    public function testNormalizePath()
    {
        // todo: implement
    }

    public function testCreateModel()
    {
        // todo: implement
    }

    public function testSaveModel()
    {
        // todo: implement
    }

    public function testLoadModel()
    {
        // todo: implement
    }

    public function testLoadModelByFileId()
    {
        // todo: implement
    }

    public function testDeleteModel()
    {
        // todo: implement
    }

    public function testOpenImage()
    {
        // todo: implement
    }

    public function testOpenImageWithPreset()
    {
        // todo: implement
    }

    public function testResolveRawPath()
    {
        // todo: implement
    }

    public function testResolveCachePath()
    {
        // todo: implement
    }

    public function testResolveCacheUrl()
    {
        // todo: implement
    }

    public function testResolveHolderPath()
    {
        // todo: implement
    }

    public function testResolveHolderUrl()
    {
        // todo: implement
    }

    public function testGetBasePath()
    {
        $manager = $this->getManager();
        $this->assertEquals('files/images', $manager->getBasePath());
        $this->assertNotEquals(false, strpos($manager->getBasePath(true), 'yii-imagemanager/tests/files/images'));
    }

    public function testGetBaseUrl()
    {
        $manager = $this->getManager();
        $this->assertEquals('files/images', $manager->getBaseUrl());
        $this->assertEquals('test/files/images', $manager->getBaseUrl(true));
    }

    public function testGetFactory()
    {
        $manager = $this->getManager();
        $this->assertTrue($manager->getFactory() instanceof Imagine\Image\ImagineInterface);
    }

    /**
     * @return ImageManager
     */
    protected function getManager()
    {
        return Yii::app()->getComponent('imageManager');
    }
}