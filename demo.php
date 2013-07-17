<?php

require 'vendor/autoload.php';

use \NilPortugues\SEO\ImageHandler\Classes\DataRecord\ImageDataRecordPDO as ImageDataRecordPDO;
use \NilPortugues\SEO\ImageHandler\Classes\ImageHTMLParser as ImageHTMLParser;
use \NilPortugues\SEO\ImageHandler\Classes\ImageFileManager as ImageFileManager;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObject as ImageObject;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObjectCollection as ImageObjectCollection;
use \NilPortugues\SEO\ImageHandler\ImageHandler as ImageHandler;

/***********************************************************************
 * Demo Set Up
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

//Set up the object depencencies to inject into the ImageHandler instance
$idr = new ImageDataRecordPDO($db);
$ihp = new ImageHTMLParser();
$ifm = new ImageFileManager();
$io = new ImageObject();
$ioc = new ImageObjectCollection();

//Instantiation of ImageHandler
$imageHandler = new ImageHandler($ihp, $ifm, $io, $ioc, $idr);

/***********************************************************************
 * Usage
 *************************************************************************/
$html = <<<HTML
    <p>Images with height or width not matching the original source image will be resized and created as a new image.</p>

    <!-- Image in comment tags shouldn't be stripped or processed. Simply ignored.
    <img src="https://www.google.com/images/srpr/logo3w.png">
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/jAune_new_p11_01.jpg">
    -->

    <!-- BASIC CASES -->
    <img src="https://www.google.com/images/srpr/logo3w.png">
    <img src="https://www.google.com/images/srpr/logo3w.png">
    <br>
    <img src="https://www.google.com/images/srpr/logo3w.png" style="width:320px;height:60px;border-radius: 6px;">
    <img src="https://www.google.com/images/srpr/logo3w.png" style="border-radius: 6px;height:50px;">
    <br>
    <img src="https://www.google.com/images/srpr/logo3w.png" style="border-radius: 6px;width:150px;">
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" style="">
    <br>
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" style="height:65px">
    <!-- THIS IMAGE DOES NOT EXIST -->
    <img src="http://google.com/image/does/not/exist.jpg">
    <br>
    <!-- SPECIAL CASE 1: HEIGHT OR WIDTH ATTR SHOULD BE USED IF NO WIDTH OR HEIGHT ARE DEFINED IN THE STYLE ATTRIBUTE-->
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" width="80" style="">
    <!-- SPECIAL CASE 2: DUPLICATE WIDTH OR HEIGHT WILL BE REMOVED. CSS VALUE WILL BE USED AS THEY HAVE PRIORITY OVER ATTRIBUTE VALUES. -->
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" width="80" style="width:115px">
    <br>
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" height="80" style="height:75px">
    <!-- SPECIAL CASE 3: WIDTH ATTR. WILL BE REMOVED. CSS VALUE WILL BE USED AS THEY HAVE PRIORITY OVER ATTRIBUTE VALUES.  -->
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" width="50%" style="WIDTH:20%; border:2px solid red">
    <br>
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" width="50%" style="WIDTH:50px; border:2px solid red">
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" width="396" height="636" style="WIDTH:25%; border:2px solid red">
    <br>
    <!-- SPECIAL CASE 4: PERCENTAGES IMAGES MUST BE IGNORED. IMAGE SIZE WILL DEPEND ON THE IMAGE WRAPPER ELEMENT -->
    <img src="https://www.google.com/images/srpr/logo3w.png" style="border-radius: 6px;height:33%;; border:2px solid red">
    <img src="https://www.google.com/images/srpr/logo3w.png" style="border-radius: 6px;width:33%;; border:2px solid red">
    <br>
    <!-- SPECIAL CASE 5: HEIGHT VALUE SHOULD BE KEPT IF PRESENT. PUT IT INSIDE STYLE SO IT DOESN'T GET REMOVED. -->
    <img src="./images/ubuntu-logo14.png" data-attribute="example1" width="396" height="636" style="WIDTH:25%; border:2px solid red">

HTML;

$newHtml = $imageHandler->getParsedHtml($html, $baseDir, $downloadDir);
$recoveredHtml = $imageHandler->getHtml($newHtml, $imageDomainPath);

echo "<h1>Original</h1>\n";
echo '<textarea style="width:100%; height:300px">' . $html . '</textarea>';
echo "\n";
echo $html;
echo "\n\n\n\n\n";

echo "<h1>Processed</h1>\n";
echo '<textarea style="width:100%; height:300px">' . $newHtml . '</textarea>';
echo "\n\n\n\n\n";

echo "<h1>Recovered</h1>\n";
echo '<textarea style="width:100%; height:300px">' . $recoveredHtml . '</textarea>';
echo "\n";
echo $recoveredHtml;
echo "\n\n\n\n\n";

?>

<a href="https://github.com/nilopc/NilPortugues_PHP_SEO_ImageHandler">
    <img style="position: fixed; top: 0; right: 0; border: 0;"
         src="https://s3.amazonaws.com/github/ribbons/forkme_right_darkblue_121621.png" alt="Fork me on GitHub">
</a>
