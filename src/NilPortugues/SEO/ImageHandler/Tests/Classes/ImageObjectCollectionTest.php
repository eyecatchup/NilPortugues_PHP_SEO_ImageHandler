<?php
namespace NilPortugues\SEO\ImageHandler\Tests\Classes;

use  \NilPortugues\SEO\ImageHandler\Classes\ImageObject as ImageObject;

class ImageObjectCollectionTest extends \PHPUnit_Framework_TestCase
{
    protected $ImageObjectCollection;
    protected $ImageObject;

    public function setUp()
    {
        $this->ImageObjectCollection = new \NilPortugues\SEO\ImageHandler\Classes\ImageObjectCollection();
    }

    public function testAddImageObjectToImageObjectCollection()
    {
        $testCase = new ImageObject();
        $this->ImageObjectCollection->addObject($testCase);

        $result = $this->ImageObjectCollection->getCollection();
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
    }

    public function testAddNonValidDataToImageObjectCollection()
    {
        $testCase = array();
        $this->setExpectedException('Exception');
        $this->ImageObjectCollection->addObject($testCase);
    }

    public function testImageObjectCollectionIsEmpty()
    {
        $result = $this->ImageObjectCollection->emptyCollection()->getCollection();
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
        $this->assertCount(0, $result);
    }

    public function testImageObjectCollectionAddAndRemoveImageObjectAndIsEmpty()
    {
        $result = $this->ImageObjectCollection->addObject(new ImageObject())->emptyCollection()->getCollection();
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
        $this->assertCount(0, $result);
    }

    public function testDeleteImageObjectFromImageObjectCollection()
    {
        $testCase = new ImageObject();
        $this->ImageObjectCollection->addObject($testCase);

        $this->ImageObjectCollection->deleteObject(0);
        $result = $this->ImageObjectCollection->emptyCollection()->getCollection();

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
        $this->assertCount(0, $result);
    }

    public function testTryDeletingNonExistentImageObjectFromImageObjectCollectionWhenAlreadyEmpty()
    {
        $this->ImageObjectCollection->emptyCollection();
        $this->setExpectedException('Exception');
        $this->ImageObjectCollection->deleteObject(1000);
    }

    public function testFindExistingImageObject()
    {
        $testCase = new ImageObject();
        $this->ImageObjectCollection->addObject($testCase);

        $result = $this->ImageObjectCollection->findObject(0);
        $this->assertInstanceOf('\NilPortugues\SEO\ImageHandler\Classes\ImageObject', $result);
    }

    public function testFindNonExistentImageObject()
    {
        $this->ImageObjectCollection->emptyCollection();
        $this->setExpectedException('Exception');
        $this->ImageObjectCollection->findObject(1000);
    }

    public function testSetObjectKeyForExistingKeyInCollection()
    {
        $testCase = new ImageObject();
        $this->ImageObjectCollection->addObject($testCase);

        $this->ImageObjectCollection->setObjectKey(0, 1000);
        $result = $this->ImageObjectCollection->findObject(1000);
        $this->assertInstanceOf('\NilPortugues\SEO\ImageHandler\Classes\ImageObject', $result);
    }

    public function testSetObjectKeyForNonExistantKeyInCollection()
    {
        $this->ImageObjectCollection->emptyCollection();
        $this->setExpectedException('Exception');
        $this->ImageObjectCollection->setObjectKey(1000, 1001);
    }

    public function testSetObjectKeyWithTheSameKeyInCollection()
    {
        $testCase = new ImageObject();
        $this->ImageObjectCollection->addObject($testCase);

        $this->ImageObjectCollection->setObjectKey(0, 0);
        $result = $this->ImageObjectCollection->findObject(0);
        $this->assertInstanceOf('\NilPortugues\SEO\ImageHandler\Classes\ImageObject', $result);
    }

    public function tearDown()
    {
        $this->ImageObjectCollection = NULL;
    }
}
