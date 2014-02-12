<?php

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
                        'baseUrl' => '//test',
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

    public function testCreatePresetOptions()
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
        $manager = $this->getManager();
        $this->assertEquals('/path/to/test.png', $manager->normalizePath('files/images/raw/path/to/test.png'));
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
        $manager = $this->getManager();
        $this->assertEquals('files/images/raw', $manager->resolveRawPath());
        $this->assertNotEquals(false, strpos($manager->resolveRawPath(true), '/files/images'));
    }

    public function testResolveCachePath()
    {
        $manager = $this->getManager();
        $this->assertEquals('files/images/cache', $manager->resolveCachePath());
        $this->assertNotEquals(false, strpos($manager->resolveCachePath(true), '/files/images/cache'));
    }

    public function testResolveCacheUrl()
    {
        $manager = $this->getManager();
        $this->assertEquals('files/images/cache', $manager->resolveCacheUrl());
        $this->assertEquals('//test/files/images/cache', $manager->resolveCacheUrl(true));
    }

    public function testResolveHolderPath()
    {
        $manager = $this->getManager();
        $this->assertEquals('files/images/holder', $manager->resolveHolderPath());
        $this->assertNotEquals(false, strpos($manager->resolveHolderPath(true), '/files/images/holder'));
    }

    public function testResolveHolderUrl()
    {
        $manager = $this->getManager();
        $this->assertEquals('files/images/holder', $manager->resolveHolderUrl());
        $this->assertEquals('//test/files/images/holder', $manager->resolveHolderUrl(true));
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
        $this->assertEquals('//test/files/images', $manager->getBaseUrl(true));
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