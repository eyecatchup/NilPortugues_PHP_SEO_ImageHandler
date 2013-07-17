# NilPortugues\SEO\ImageHandler  [![Build Status](https://travis-ci.org/nilopc/NilPortugues_PHP_SEO_ImageHandler.png?branch=master)](https://travis-ci.org/nilopc/NilPortugues_PHP_SEO_ImageHandler)

## Purpose
This PHP class that extracts image tags from HTML code and allows keeping a record of the processed images in a data structure such as a database, creating a abstraction layer that allows the user handle its web application images. The main reason you would do this is SEO.

## How it works
This class is meant to be used both in the back-end and front-end of a site.
 * **Back-end**
     * In the backend, this class should handle processing large chuncks of HTML input.
        * Processing HTML means replacing valid HTML image tags for custom image tag placeholders. This process is reversed in the front-end.
        * Processing HTML means resizing images that have been enlarged or reduced using pixel measures.
        * Processing HTML means downloading external images and resizing them as previously explained if necessary.
        * Processing HTML means making sure no two identical images will be sitting on the server's image directory or will be loaded from other sources. All images are unique and duplicates are disregarded if these exist in the image database.
    * By processing the images, and separating the images from the actual content, all images are now manipulable.
    * By coding a CRUD interface in a back-end (**not provided**), an authorized user can edit alt attribute, title attribute and  the image file name values. This changes can be stored in a database and will be reflected elsewhere when replacing the placeholders with the real data.
 * **Front-end**
    * In the front-end, this class should be only used to replace the image placeholders for their valid HTML image tags equivalents.

## Data Structures or Databases
There's no need to use MySQL, and any other database (PostgreSQL, MariaDB,...) or storage system (Redis, flat files...) can be used.

Choose your data record technology by implementing an class implementing the `ImageDataRecordInterface.php` methods. Using this interface, it will allow you to use ORMs such as Doctrine2.

By default, this class includes a SQL (MySQL) database structure to be used with this code. This file can be found at `src/NilPortugues/SEO/ImageHandler/Resources/ImageTable.sql`.

## Code Usage
### Back-end
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

### Front-end

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
Image file name is **terrible** for SEO purposes. The reason behind this name is that the file name it's actually the file's md5 file hash. This is used to assure there's no image being overridden or repeated in our image directory.

If the image file name concerns you, which should, you should build in your application file renaming and let `6fd86da74659f04253285e853af26845.jpg` be whatever you want it to be to meet your SEO purposes.

Finally, if **title** and **alt** attributes are provided - whether these were extracted values or entered by an application into the data record row- the class will also create and populate them when processing `$processedHtml` to `$recoveredHtml`, which is really good for SEO.

As a bonus, when rebuilding the image tags, **width** and **height** attributes are added. While there's no real SEO benefits on doing this, it will cope for faster rendering times in the browser. If image was down-scaled or up-scaled, these values will match the scaled image dimensions.

## Todo:
* Add a method to check if the download directory exists
* Add a method to check if the download directory is writable.
* Add a method checking if current file has writing permittions.
* Add proper documentation.


## Author
Nil Portugués Calderó
 - <contact@nilportugues.com>
 - http://nilportugues.com
