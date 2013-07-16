<?php
namespace NilPortugues\SEO\ImageHandler\Tests\Classes;


class ImageObjectTest extends \PHPUnit_Framework_TestCase
{
    protected $ImageObject;

    public function setUp()
    {
        $this->ImageObject = new \NilPortugues\SEO\ImageHandler\Classes\ImageObject();
    }

    public function testAddOtherAppearances()
    {
        $testCase = 'test';
        $expected = 'test';

        $this->ImageObject->addOtherAppearances($testCase);

        $result = $this->ImageObject->getOtherAppearancesArray();
        $this->assertInternalType('array', $result);
        $this->assertContains($expected, $result);
        $this->assertCount(1, $result);
        $this->assertEquals(array($expected), $result);
    }

    public function testGetOtherAppearancesArrayWhenArrayIsEmpty()
    {
        $result = $this->ImageObject->getOtherAppearancesArray();
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
        $this->assertCount(0, $result);
        $this->assertEquals(array(), $result);
    }

    public function testAddRepeatedOtherAppearancesValueAndReturnOnlyOneAsAResult()
    {
        $testCase = 'test';
        $expected = 'test';

        //Add 4 times the same value
        $this->ImageObject->addOtherAppearances($testCase);
        $this->ImageObject->addOtherAppearances($testCase);
        $this->ImageObject->addOtherAppearances($testCase);
        $this->ImageObject->addOtherAppearances($testCase);

        $result = $this->ImageObject->getOtherAppearancesArray();
        $this->assertInternalType('array', $result);
        $this->assertContains($expected, $result);
        $this->assertCount(1, $result);
        $this->assertEquals(array($expected), $result);
    }


    public function tearDown()
    {
        $this->ImageObject = NULL;
    }
}
