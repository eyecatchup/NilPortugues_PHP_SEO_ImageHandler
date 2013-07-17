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

    //Images created
    protected $images = array();

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

    public function testConvertImageTagsToCustomTags()
    {
        $html = '<img src="' . $this->testImage . '" data-attribute="example1">';

        $md5_hash = md5_file($this->baseDir . $this->testImage);
        $expected = '{{IMG|' . $md5_hash . '|data-attribute="example1"}}';

        $newHtml = $this->imageHandler->getParsedHtml($html, $this->baseDir, $this->downloadDir);
        $this->assertEquals($expected, $newHtml);
    }

    public function testConvertImageTagsToCustomTagsRemoveWidth()
    {
        $html = '<img src="' . $this->testImage . '" style="width:250px" data-attribute="example1">';

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

    public function testPersistExistingImageWithImageDomainSrcAndGetImageData()
    {

    }

    public function testPersistExistingImageWithRelativeSrcAndGetImageData()
    {

    }

    public function testPersistExistingImageWithAbsoluteSrcAndGetImageData()
    {

    }

    public function testPersistNewImageFromExternalSourceAndGetImageData()
    {

    }

    public function tearDown()
    {
        $this->idr = NULL;
        $this->ihp = NULL;
        $this->ifm = NULL;
        $this->io = NULL;
        $this->ioc = NULL;
    }
}


class PDOMock extends \PDO
{
    public function __construct()
    {
    }
}
