<?php
namespace NilPortugues\SEO\ImageHandler\Tests;

use \NilPortugues\SEO\ImageHandler\Classes\DataRecord\ImageDataRecordPDO as ImageDataRecordPDO;
use \NilPortugues\SEO\ImageHandler\Classes\ImageHTMLParser as ImageHTMLParser;
use \NilPortugues\SEO\ImageHandler\Classes\ImageFileManager as ImageFileManager;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObject as ImageObject;
use \NilPortugues\SEO\ImageHandler\Classes\ImageObjectCollection as ImageObjectCollection;
use \NilPortugues\SEO\ImageHandler\ImageHandler as ImageHandler;

class ImageHandlerTest extends \PHPUnit_Framework_TestCase
{
    //Dependency Injection vars
    protected $db;
    protected $idr;
    protected $ihp;
    protected $ifm;
    protected $io;
    protected $ioc;
    protected $imageHandler;

    //Environment vars
    protected $baseDir = './';
    protected $downloadDir = 'images/downloaded';
    protected $imageDomain = 'http://static.blog.local/';

    //Database
    protected $dbType = 'mysql';
    protected $dbHost = 'localhost';
    protected $dbName = 'sonrisaProject';
    protected $dbUsername = 'root';
    protected $dbPassword = 'root';

    public function setUp()
    {
        //Set up database connection
        $dns = $this->dbType . ':dbname=' . $this->dbName . ';host=' . $this->dbHost;
        $this->db = new \PDO($dns, $this->dbUsername, $this->dbPassword);
        $this->idr = ImageDataRecordPDO::getInstance($this->db);

        //Build object Instances.
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
        $this->db = NULL;
        $this->idr = NULL;
        $this->ihp = NULL;
        $this->ifm = NULL;
        $this->io = NULL;
        $this->ioc = NULL;
    }
}