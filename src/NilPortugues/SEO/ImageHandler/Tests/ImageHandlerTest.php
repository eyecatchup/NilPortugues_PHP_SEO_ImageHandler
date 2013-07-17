<?php
namespace NilPortugues\SEO\ImageHandler\Tests;

use \NilPortugues\SEO\ImageHandler\Classes\ImageHTMLParser as ImageHTMLParser;
use \NilPortugues\SEO\ImageHandler\Classes\ImageFileManager as ImageFileManager;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObject as ImageObject;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObjectCollection as ImageObjectCollection;
use \NilPortugues\SEO\ImageHandler\ImageHandler as ImageHandler;

class ImageHandlerTest extends \PHPUnit_Framework_TestCase
{
    //Dependency Injection vars
    protected $idr;
    protected $ihp;
    protected $ifm;
    protected $io;
    protected $ioc;
    protected $imageHandler;

    //Environment vars
    protected $baseDir = './';
    protected $downloadDir = 'images/downloaded';
    protected $imageDomain = 'http://static.mydomain.com/';
    protected $testImage = 'images/m2x.png';

    public function setUp()
    {
        //Set up a MOCK database connection.
        $this->idr =
            $this->getMockBuilder('\NilPortugues\SEO\ImageHandler\Classes\DataRecord\ImageDataRecordPDO')
                ->disableOriginalConstructor()
                ->getMock();

        //Build object instances and inject them.
        $this->ihp = new ImageHTMLParser();
        $this->ifm = new ImageFileManager();
        $this->io = new ImageObject();
        $this->ioc = new ImageObjectCollection();
        $this->imageHandler = new ImageHandler($this->ihp, $this->ifm, $this->io, $this->ioc, $this->idr);

    }

    public function tearDown()
    {
        $this->idr = NULL;
        $this->ihp = NULL;
        $this->ifm = NULL;
        $this->io = NULL;
        $this->ioc = NULL;
    }

    //__________________________________________________________________
    //
    // $this->imageHandler->getParsedHtml() TEST CASES
    //__________________________________________________________________

    public function testConvertLocalImageTagsToCustomTags_1()
    {
        $html = '<img src="' . $this->testImage . '" data-attribute="example1">';

        $md5_hash = md5_file($this->baseDir . $this->testImage);
        $expected = '{{IMG|' . $md5_hash . '|data-attribute="example1"}}';

        $newHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertEquals($expected, $newHtml);
    }

    public function testConvertLocalImageTagsToCustomTags_2()
    {
        $html = '<img src="./' . $this->testImage . '" data-attribute="example1">';

        $md5_hash = md5_file($this->baseDir . $this->testImage);
        $expected = '{{IMG|' . $md5_hash . '|data-attribute="example1"}}';

        $newHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertEquals($expected, $newHtml);
    }

    public function testConvertLocalImageTagsToCustomTags_3()
    {
        $html = '<img src="//' . str_replace(array('http://', 'https://'), '//', $this->imageDomain) . $this->testImage . '" style="width:250px" data-attribute="example1">';

        $newHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertNotContains('width:250px', $newHtml);
    }

    public function testConvertImageTagsToCustomTagsRemoveHeight()
    {
        $html = '<img src="' . $this->testImage . '" style="height:250px" data-attribute="example1">';

        $newHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertNotContains('height:250px', $newHtml);
    }

    public function testConvertImageTagsToCustomTagsPreserveStyleWithoutHeight()
    {
        $html = '<img src="' . $this->testImage . '" style="height:250px; border:1px solid red" data-attribute="example1">';

        $newHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertNotContains('height:250px', $newHtml);
        $this->assertContains('style="border:1px solid red"', $newHtml);
    }

    public function testConvertImageTagsToCustomTagsWithResize()
    {
        $html = '<img src="' . $this->testImage . '" style="height:250px; border:1px solid red; width:250px;" data-attribute="example1">';
        $md5_hash = md5_file($this->baseDir . $this->testImage);
        $expected = '{{IMG|' . $md5_hash . '|data-attribute="example1"}}';

        $newHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertNotEquals($expected, $newHtml);
        $this->assertNotContains('height:250px', $newHtml);
        $this->assertNotContains('width:250px', $newHtml);
        $this->assertContains('style="border:1px solid red"', $newHtml);
    }

    public function testConvertTwoIdenticalImageTagsToCustomTags()
    {
        $html = '
        <img src="' . $this->testImage . '" data-attribute="example1">
        <img src="' . $this->testImage . '" data-attribute="example1">';

        $md5_hash = md5_file($this->baseDir . $this->testImage);
        $expected = '
        {{IMG|' . $md5_hash . '|data-attribute="example1"}}
        {{IMG|' . $md5_hash . '|data-attribute="example1"}}';

        $newHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertEquals($expected, $newHtml);
    }

    public function testConvertNonExistentImageAndFromExternalSourceToCustomTags_1()
    {
        $html = '<img src="http://google.com/image/does/not/exist.jpg">';
        $recoveredHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertEquals('', $recoveredHtml);
    }

    public function testConvertNonExistentImageAndFromExternalSourceToCustomTags_2()
    {
        $html = '<img src="//google.com/image/does/not/exist.jpg">';
        $recoveredHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertEquals('', $recoveredHtml);
    }

    //__________________________________________________________________
    //
    // $this->imageHandler->getParsedHtml() TEST CASES
    //__________________________________________________________________

    public function testConvertCustomTagsToImageTagsWhenImageExistsInDataRecord()
    {
        //test case
        $md5_hash = md5_file($this->baseDir . $this->testImage);
        list($width, $height) = getimagesize($this->baseDir . $this->testImage);

        //mock function set up
        $this->idr
            ->expects($this->any())
            ->method('getExistingImageHashes')
            ->will
        ($this->returnValue(
            array
            (
                0 => array
                (
                    'file_md5' => $md5_hash,
                    'width' => $width,
                    'height' => $height,
                    'filepath' => $this->testImage,
                    'alt' => '',
                    'title' => '',
                ),
            )
        ));

        $html = '{{IMG|' . $md5_hash . '|data-attribute="example1"}}';
        $expected = '<img src="' . str_replace(array('http://', 'https://'), '//', $this->imageDomain) . $this->testImage . '" width="' . $width . '" height="' . $height . '" data-attribute="example1">';

        $recoveredHtml = $this->imageHandler->getHtml($html, $this->imageDomain);
        $this->assertEquals($expected, $recoveredHtml);
    }

    public function testConvertCustomTagsToImageTagsWhenImageNonExistentInDataRecord()
    {
        //mock function set up
        $this->idr
            ->expects($this->any())
            ->method('getExistingImageHashes')
            ->will
        ($this->returnValue(array()));

        //This md5_hash shouldn't be in the data record. So let's create a fake one.
        $html = '{{IMG|' . md5(0) . '|data-attribute="example1"}}';

        $recoveredHtml = $this->imageHandler->getHtml($html, $this->imageDomain);
        $this->assertEquals('', $recoveredHtml);
    }

    //__________________________________________________________________
    //
    // $this->imageHandler->addImage() TEST CASES
    //__________________________________________________________________

    public function testPersistExistingImageWithLocalSrcAndGetImageData_1()
    {
        //mock function set up
        $md5_hash = md5_file($this->baseDir . $this->testImage);
        list($width, $height) = getimagesize($this->baseDir . $this->testImage);
        $this->idr
            ->expects($this->any())
            ->method('getExistingImageHashes')
            ->will
        ($this->returnValue(
            array
            (
                0 => array
                (
                    'file_md5' => $md5_hash,
                    'width' => $width,
                    'height' => $height,
                    'filepath' => $this->testImage,
                    'alt' => '',
                    'title' => '',
                ),
            )
        ));

        $imgSrc = $this->testImage;
        $data = $this->imageHandler->addImage($imgSrc, $this->baseDir, $this->downloadDir, $this->imageDomain);
        $this->assertNotEmpty($data);
        $this->assertInternalType('array', $data);
    }

    public function testPersistExistingImageWithLocalSrcAndGetImageData_2()
    {
        //mock function set up
        $md5_hash = md5_file($this->baseDir . $this->testImage);
        list($width, $height) = getimagesize($this->baseDir . $this->testImage);
        $this->idr
            ->expects($this->any())
            ->method('getExistingImageHashes')
            ->will
        ($this->returnValue(
            array
            (
                0 => array
                (
                    'file_md5' => $md5_hash,
                    'width' => $width,
                    'height' => $height,
                    'filepath' => $this->testImage,
                    'alt' => '',
                    'title' => '',
                ),
            )
        ));

        $imgSrc = './' . $this->testImage;

        $data = $this->imageHandler->addImage($imgSrc, $this->baseDir, $this->downloadDir, $this->imageDomain);
        $this->assertNotEmpty($data);
        $this->assertInternalType('array', $data);
    }

    public function testPersistExistingImageWithLocalSrcAndGetImageData_3()
    {
        //mock function set up
        $md5_hash = md5_file($this->baseDir . $this->testImage);
        list($width, $height) = getimagesize($this->baseDir . $this->testImage);

        $this->idr
            ->expects($this->any())
            ->method('getExistingImageHashes')
            ->will
        ($this->returnValue(
            array
            (
                0 => array
                (
                    'file_md5' => $md5_hash,
                    'width' => $width,
                    'height' => $height,
                    'filepath' => $this->testImage,
                    'alt' => '',
                    'title' => '',
                ),
            )
        ));

        $imgSrc = '/' . $this->testImage;

        $data = $this->imageHandler->addImage($imgSrc, $this->baseDir, $this->downloadDir, $this->imageDomain);
        $this->assertNotEmpty($data);
        $this->assertInternalType('array', $data);

    }

    public function testPersistNonExistentLocalImageWithImageDomainSrcAndGetImageData()
    {
        $imgSrc = $this->imageDomain . $this->testImage;
        $data = $this->imageHandler->addImage($imgSrc, $this->baseDir, $this->downloadDir, $this->imageDomain);
        $this->assertFalse($data);
    }

    public function testPersistNonExistentExternalImageWithImageDomainSrcAndGetImageData()
    {
        $imgSrc = "http://google.com/image/does/not/exist.jpg";
        $data = $this->imageHandler->addImage($imgSrc, $this->baseDir, $this->downloadDir, $this->imageDomain);
        $this->assertFalse($data);

        $imgSrc = "//google.com/image/does/not/exist.jpg";
        $data = $this->imageHandler->addImage($imgSrc, $this->baseDir, $this->downloadDir, $this->imageDomain);
        $this->assertFalse($data);
    }
}

class PDOMock extends \PDO
{
    public function __construct()
    {
    }
}
