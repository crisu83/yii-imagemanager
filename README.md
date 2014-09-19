yii-imagemanager
================

[![Latest Stable Version](https://poser.pugx.org/crisu83/yii-imagemanager/v/stable.png)](https://packagist.org/packages/crisu83/yii-imagemanager)
[![Build Status](https://travis-ci.org/crisu83/yii-imagemanager.png)](https://travis-ci.org/crisu83/yii-imagemanager)

Image manager extension for the Yii PHP framework.

## Introduction

I started this project to reduce the need for boilerplate code when working with images in my Yii applications.
The goal was not to provide an user interface for image management but to wrap the powerful Imagine library using
Yii's conventions. Imagine is one of the best image manipulation libraries for PHP and comes with a wide range
of different image filters and supports all the major graphics libraries. 

This extension is an extension to my [yii-filemanager](https://github.com/Crisu83/yii-filemanager) extension.

## Features

* A wide range of image filters such as crop, resize and thumbnail
* Image presets with support for caching of generated images
* Support for both server and client -side (through [holder.js](http://imsky.github.io/holder/)) placeholders
* Storing of uploaded images in the database
* Supports multiple graphics libraries including GD, Imagick and Gmagick
* Application component that provides centralized access
* Active record behavior to ease working with the API

## Setup

The easiest way to get started with the yii-imagemanager is to install it using Composer.
That way Composer will take care of installing its dependancies, the yii-filemanager and Imagine.
Alternatively you can download the extension and it's dependencies manually.
Just make sure that all the libraries are registered with the autoloader.

Add the following rows your composer.json file:

```js
"require": {
  .....
  "crisu83/yii-imagemanager": "dev-master"
},
"minimum-stability": "dev",
```

Run the following command in the root directory of your project:

```bash
php composer.phar install
```

Add the image manager application component to your application configuration:

```php
'components' => array(
  .....
  'imageManager' => array(
    'class' => 'vendor.crisu83.yii-imagemanager.components.ImageManager',
    'presets' => array(
      'myPreset' => array(
        'filters' => array(
          array('thumbnail', 'width' => 160, 'height' => 90, 'mode' => 'outbound'),
        ),
      ),
    ),
    'holders' => array(
      'default' => 'placeholder.png',
    ),
  ),
),
```

The following configuration parameters are available for the image manager:

* **driver** the image driver to use, valid drivers are ```gd```, ```imagick``` and ```gmagick```
* **presets** the preset filter configurations (name => config)
* **holders** the placeholder image configurations (name => filename)
* **imageDir** the name of the images directory
* **rawDir** the name of the directory with the unmodified images
* **cacheDir** the name of the direcotry with the cached images
* **holderDir** the name of the directory with placeholder images
* **clientHolderText** the text used with client-side placeholders
* **modelClass** the name of the image model class
* **dependencies** the map over dependency paths (name => path)
* **filterManagerID** the component ID for the file manager component

Add the image command to your console application configuration:

```php
'commandMap' => array(
  .....
  'image' => array(
    'class' => 'vendor.crisu83.yii-imagemanager.commands.ImageCommand',
  ),
),
```

Run the following command through yiic:

```bash
yiic image createAccessFile --baseUrl="<base/url>" --path="<path/to/images>"
```

The following arguments are available for the createAccessFile action:

* **baseUrl** the rewrite base url to use
* **path** the full path to the access file to create

## What's included?

* **ImageBehavior** behavior that ease saving, rendering and deleting of images associated with active records
* **ImageCommand** console command for running shell tasks
* **ImageManager** application component that provides centralized access
* **ImagePreset** component that defines a single image preset
* **ImageController** controller for running actions via an URL
* **ImagineFilter** the base class for all the image filters
* **Image** model class for the image table

## Getting started

Once your have configured everything you are ready to start using the image manager. Below I will try to explain the most basic features and how to use them.

### Configure presets and placeholders

We need to add a few more things to your application configuration before we can begin. In this example we will add a preset and a placeholder image to the image manager. Add the following lines to your application configuration:

```php
  'imageManager' => array(
    .....
    'presets' => array(
      'product' => array(
        'filters' => array(
          array('thumbnail', 'width' => 220, 'height' => 220, 'mode' => 'outbound'),
        ),
      ),
    ),
    'holders' => array(
      'default' => 'placeholder.png', // you need to add an image named placeholder.png in the images/holder directory for this
    ),
  ),
```

### Attach the image behavior to your model

Let us assume that you have a model called **Product** for which you want to upload images. In order to do so we need to add an **imageId** column to your user table where we can store the id for the associated image model. To attach the behavior we add the following code to the **Product** class:

```php
/**
 * .....
 * Methods accessible through the 'ImageBehavior' class:
 * @method CUploadedFile getUploadedImage()
 * @method Image saveImage($file, $name = null, $path = null, $scenario = 'insert')
 * @method string renderImagePreset($name, $alt = '', $htmlOptions = array(), $holder = null)
 * @method string createImagePresetUrl($name, $holder = null)
 * @method boolean deleteImage()
 */
class Product extends CActiveRecord
{
  /**
   * @var CUploadedFile the uploaded file (used when uploading a product image).
   */
  public $upload;
  
  /**
   * @return array the behavior configurations (behavior name=>behavior config).
   */
  public function behaviors()
  {
    return array(
      'image' => array(
        'class' => 'vendor.crisu83.yii-imagemanager.behaviors.ImageBehavior',
        'name' => $this->name,
        'path' => 'products',
      ),
    );
  }
  
  /**
   * @return array relational rules.
   */
  public function relations()
  {
    return array(
      'image' => array(self::BELONGS_TO, 'Image', 'imageId'),
    );
  }
  
  /**
   * @return array validation rules for model attributes.
   */
  public function rules()
  {
    return array(
      ......
      array('upload', 'file'), // configure the validator if necessary
    );
  }
  
  /**
   * @return array customized attribute labels (name=>label).
   */
  public function attributeLabels()
  {
    return array(
      'upload' => 'Image',
    );
  }
  
  .....
}
```

### Uploading and saving the image

Alright, now we can save images through the **Product** model. Next we will add an action that renders a view that contains a form with a file input. When the form is submitted the uploaded image should be saved in the database. Here is the code for both the action and the view:

```php
class ProductController extends Controller
{
  .....

  /**
   * Displays the page for editing the details for a specific product.
   */
  public function actionUpdate($id)
  {
    $model = Product::model()->findByPk($id);
    if (isset($_POST['Product']) {
      $model->attributes = $_POST['Product'];
      if ($model->save()) {
        $this->redirect(array('admin'));
      }
    }
    $this->render('update', array('model' => $model);
  }
}
```

```php
<?php
/* @var ProductController $this */
/* @var Product $model */
/* @var CActiveForm $form */
?>
<div class="product-controller update-action">
  <?php $this->beginWidget('CActiveForm', array(
    'htmlOptions' => array('enctype' => 'multipart/form-data'), // don't forget this!
  )); ?>
  
    .....
  
    <?php echo $form->labelEx($model, 'uploadedFile'); ?>
    <?php echo $form->fileField($model, 'uploadedFile'); ?>
    <?php echo $form->error($model, 'uploadedFile'); ?>
    
    <div class="product-image">
      <?php echo $model->renderImagePreset('product', $model->name, array(), 'default'); ?>
    </div>
    
    <?php echo CHtml::submitButton(); ?>
  
  <?php $this->endWidget(); ?>
</div>
``` 

### There's more

This is just scratching the surface of what you can do with this extension, there are a lot of filters to explore and you can also work with the image manager API directly without the image behavior if you desire. The best way to learn to use this extension is to read through its code, especially the **ImageManager** application component. Good luck!
