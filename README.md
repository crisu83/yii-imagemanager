yii-imagemanager
================

Image manager extension for the Yii PHP framework.

## Introduction

I started this project to reduce the need for boilerplate code when working with iamges in my Yii applications.
The goal was not to provide an user interface for image management but to wrap the powerful Imagine library using
Yii's conventions. Imagine is one of the best image manipulation libraries for PHP and comes with a wide range
of different image filters and supports all the major graphics libraries. 

This extension is an addition to my yii-filemanager extension.

## Features

* A wide range of image filters such as crop, resize and thumbnail
* Image presets with support for caching of generated images
* Storing of uploaded images in the database
* Supports many graphics libraries including GD, Imagick and Gmagick
* Application component that provides centralized access
* Image model and active record behavior to ease working with the API

## Setup

The easiest way to get started with the yii-imagemanager is to install it using Composer.
That way Composer will take care of installing its dependancies, the yii-filemanager and Imagine.
Alternatively you can download the extension and it's dependencies manually.
Just make sure that all the libraries are registered with the autoloader.

Add the following row your composer.json file:

```js
"require": {
  .....
  "crisu83/yii-imagemanager": "dev-master"
}
```

Run the following command in the root directory of your project:

```bash
php composer.phar update
```

Add the image manager application component to your application configuration:

```php
'imageManager' => array(
  'class' => 'vendor.crisu83.yii-imagemanager.components.ImageManager',
  'presets' => array(
    'myPreset' => array(
       array('thumbnail', 'width' => 160, 'height' => 90, 'mode' => 'outbound'),
    ),
  ),
),
```

The following configuration parameters are available for the image manager:

* **driver** the image driver to use, valid drivers are ```gd```, ```imagick``` and ```gmagick```
* **presets** the preset filter configurations
* **imageDir** the name of the images directory
* **rawDir** the name of the directory with the unmodified images
* **cacheDir** the name of the direcotry with the cached images
* **modelClass** the name of the image model class
* **filterManagerID** the component ID for the file manager component

## What's included?

* **ImageBehavior** behavior that ease saving, rendering and deleting of images associated with active records
* **ImageManager** application component that provides centralized access
* **ImageController** controller that allows generation of presets via URL
* **ImagineFilter** the base class for all the image filters
* **ImagineFilterChain** list class that allows you to chain image filters
* **Image** model class for the image table
