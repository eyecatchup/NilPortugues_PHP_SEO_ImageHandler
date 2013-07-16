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
$idr = ImageDataRecordPDO::getInstance($db);
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
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg">
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/jAune_new_p11_01.jpg">
    -->

    <!-- BASIC CASES -->
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg">
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg">
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg" style="width:300px;height:600px;border-radius: 6px;">
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg" style="border-radius: 6px;height:100px;">
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg" style="border-radius: 6px;width:100px;">
    <img src="./images/m2x.png" data-attribute="example1" style="">
    <img src="./images/m2x.png" data-attribute="example1" style="height:500px">

    <!-- SPECIAL CASE 1: HEIGHT OR WIDTH ATTR SHOULD BE USED IF NO WIDTH OR HEIGHT ARE DEFINED IN THE STYLE ATTRIBUTE-->
    <img src="./images/m2x.png" data-attribute="example1" width="150" style="">

    <!-- SPECIAL CASE 2: DUPLICATE WIDTH OR HEIGHT WILL BE REMOVED. CSS VALUE WILL BE USED AS THEY HAVE PRIORITY OVER ATTRIBUTE VALUES. -->
    <img src="./images/m2x.png" data-attribute="example1" width="150" style="width:200px">
    <img src="./images/m2x.png" data-attribute="example1" height="150" style="height:200px">

    <!-- SPECIAL CASE 3: WIDTH ATTR. WILL BE REMOVED. CSS VALUE WILL BE USED AS THEY HAVE PRIORITY OVER ATTRIBUTE VALUES.  -->
    <img src="./images/m2x.png" data-attribute="example1" width="50%" style="WIDTH:20%; border:2px solid red">
    <img src="./images/m2x.png" data-attribute="example1" width="50%" style="WIDTH:250px; border:2px solid red">
    <img src="./images/m2x.png" data-attribute="example1" width="396" height="636" style="WIDTH:50%; border:2px solid red">

    <!-- SPECIAL CASE 4: PERCENTAGES IMAGES MUST BE IGNORED. IMAGE SIZE WILL DEPEND ON THE IMAGE WRAPPER ELEMENT -->
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg" style="border-radius: 6px;height:33%;; border:2px solid red">
    <img src="http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg" style="border-radius: 6px;width:33%;; border:2px solid red">

    <!-- SPECIAL CASE 5: HEIGHT VALUE SHOULD BE KEPT IF PRESENT. PUT IT INSIDE STYLE SO IT DOESN'T GET REMOVED. -->
    <img src="./images/m2x.png" data-attribute="example1" width="396" height="636" style="WIDTH:50%; border:2px solid red">

HTML;

$newHtml = $imageHandler->getParsedHtml($html, $baseDir, $downloadDir);
$recoveredHtml = $imageHandler->getHtml($newHtml, $imageDomainPath);

echo "<h1>Original</h1>\n";
echo $html;
echo '<textarea style="width:100%; height:300px">' . $html . '</textarea>';
echo "\n\n\n\n\n";

echo "<h1>Processed</h1>\n";
echo '<textarea style="width:100%; height:300px">' . $newHtml . '</textarea>';
echo "\n\n\n\n\n";

echo "<h1>Recovered</h1>\n";
echo '<textarea style="width:100%; height:300px">' . $recoveredHtml . '</textarea>';
echo "\n";
echo $recoveredHtml;
echo "\n\n\n\n\n";
