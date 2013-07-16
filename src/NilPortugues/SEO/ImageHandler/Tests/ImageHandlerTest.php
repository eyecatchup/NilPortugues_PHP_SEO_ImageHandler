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

    //Images created
    protected $images = array();

    public function setUp()
    {
        //Set up database connection.
        $this->idr =
            $this->getMockBuilder('\NilPortugues\SEO\ImageHandler\Classes\DataRecord\ImageDataRecordPDO')
                ->disableOriginalConstructor()
                ->getMock();

        //Set up the database instance methods.

        //Build object instances and inject them.
        $this->ihp = new ImageHTMLParser();
        $this->ifm = new ImageFileManager();
        $this->io = new ImageObject();
        $this->ioc = new ImageObjectCollection();
        $this->imageHandler = new ImageHandler($this->ihp, $this->ifm, $this->io, $this->ioc, $this->idr);

    }

    public function testConvertImageTagsToCustomTags()
    {

    }

    public function testConvertCustomTagsToImageTags()
    {

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
