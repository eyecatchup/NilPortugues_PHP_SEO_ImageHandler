[![Build Status](https://travis-ci.org/nilopc/NilPortugues_PHP_SEO_ImageHandler.png?branch=master)](https://travis-ci.org/nilopc/NilPortugues_PHP_SEO_ImageHandler)
# NilPortugues\SEO\ImageHandler
A PHP class that extracts image tags from HTML code and allows keeping a record of the processed images in a data structure such as a database.

## Introduction
This class is meant to be used both in the back-end and front-end of a site.
 * **Back-end**
     * In the backend, this class should handle processing large chuncks of HTML input.
        * Processing HTML means replacing valid HTML image tags for custom image tag placeholders. This process is reversed in the front-end.
        * Processing HTML means resizing images that have been enlarged or reduced using pixel measures.
        * Processing HTML means downloading external images and resizing them as previously explained if necessary.
        * Processing HTML means making sure no two identical images will be sitting on the server's image directory or will be loaded from other sources. All images are unique and duplicates are disregarded if these exist in the image database.
    * By processing the images, and separating the images from the actual content, all images are now manipulable.
    * By coding a CRUD interface in a back-end (not provided), an authorized user can edit alt attribute, title attribute and  the image file name values. This changes can be stored in a database and will be reflected elsewhere when replacing the placeholders with the real data.
 * **Front-end**
    * In the front-end, this class should be only used to replace the image placeholders for their valid HTML image tags equivalents.


## Back-end Usage:
```
<?php

require 'vendor/autoload.php';

use \NilPortugues\SEO\ImageHandler\Classes\DataRecord\ImageDataRecordPDO as ImageDataRecordPDO;
use \NilPortugues\SEO\ImageHandler\Classes\ImageHTMLParser as ImageHTMLParser;
use \NilPortugues\SEO\ImageHandler\Classes\ImageFileManager as ImageFileManager;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObject as ImageObject;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObjectCollection as ImageObjectCollection;
use \NilPortugues\SEO\ImageHandler\ImageHandler as ImageHandler;

/***********************************************************************
 * Set Up.
 * It's up to you how to inject these values to the class.
 *************************************************************************/
//Set up the database connection
$dns = 'mysql:dbname=sonrisaProject;host=localhost';
$dbUsername = 'root';
$dbPassword = 'root';
$db = new \PDO($dns, $dbUsername, $dbPassword);

//Set up the directory configuration + imageDomain
$baseDir = './';
$downloadDir = 'images/downloaded';
$imageDomainPath = 'http://localhost/ImageHandlerClass';

/***********************************************************************
 * Object creation.
 *************************************************************************/
$idr = ImageDataRecordPDO::getInstance($db);
$ihp = new ImageHTMLParser();
$ifm = new ImageFileManager();
$io = new ImageObject();
$ioc = new ImageObjectCollection();
$imageHandler = new ImageHandler($ihp, $ifm, $io, $ioc, $idr);

//Process data.
$processedHtml = $imageHandler->getParsedHtml($html, $baseDir, $downloadDir);

```

Under the hood, what's actually doing is:
```
<!-- $html will be transformed to $processedHtml -->
<img src="http://example.com/path/to/image/directory/external-image.jpg" style="border:2px solid red" data-attribute="example1">

<!-- $processedHtml -->
{{IMG|6fd86da74659f04253285e853af26845|style="border:2px solid red"|data-attribute="example1"}}
```

## Front-end Usage:

The actual PHP code to use is the following:
```
<?php

require 'vendor/autoload.php';

use \NilPortugues\SEO\ImageHandler\Classes\DataRecord\ImageDataRecordPDO as ImageDataRecordPDO;
use \NilPortugues\SEO\ImageHandler\Classes\ImageHTMLParser as ImageHTMLParser;
use \NilPortugues\SEO\ImageHandler\Classes\ImageFileManager as ImageFileManager;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObject as ImageObject;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObjectCollection as ImageObjectCollection;
use \NilPortugues\SEO\ImageHandler\ImageHandler as ImageHandler;

/***********************************************************************
 * Object creation.
 *************************************************************************/
$idr = ImageDataRecordPDO::getInstance($db);
$ihp = new ImageHTMLParser();
$ifm = new ImageFileManager();
$io = new ImageObject();
$ioc = new ImageObjectCollection();
$imageHandler = new ImageHandler($ihp, $ifm, $io, $ioc, $idr);

//Retrieve the processed HTML data from a data source. Eg: database

//Rebuild the image tags.
$recoveredHtml = $imageHandler->getHtml($processedHtml, $imageDomainPath);
```
Under the hood, what it is actually doing:
```
<!-- $processedHtml will be transformed to $recoveredHtml -->
{{IMG|6fd86da74659f04253285e853af26845|style="border:2px solid red"|data-attribute="example1"}}

<!-- $recoveredHtml -->
<img src="//localhost/ImageHandlerClass/images/downloaded/6fd86da74659f04253285e853af26845.jpg" width="400" height="240" style="border:2px solid red" data-attribute="example1">
```
As you can notice, width and height attributes were added, but everything else is kept. This is for faster rendering times in the browser. If image is downscaled or upscaled, these values will match the scaled image dimensions.

If title and alt attributes are provided by the image storage source, the class will also create them when processing `$processedHtml` to `$recoveredHtml`, which is really good for SEO.

Image filename on the other hand, is *horrible for SEO*, but you can code your application code to rename image filename and `6fd86da74659f04253285e853af26845.jpg` will be whatever you want it to be.


## Todo:
* Complete ImageHandlerTest.php
* Add a Mock for the database in tests.
* Add a method to check if the download directory exists
* Add a method to check if the download directory is writable.
* Add a method checking if current file has writing permittions.
* Add proper documentation.


## Author
Nil Portugués Calderó
 - <contact@nilportugues.com>
 - http://nilportugues.com
