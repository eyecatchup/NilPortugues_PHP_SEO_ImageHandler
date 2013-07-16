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

## Front-end Usage:



## Todo:
* Complete ImageHandlerTest.php
* Add proper documentation.


## Author
Nil Portugués Calderó
 - <contact@nilportugues.com>
 - http://nilportugues.com
