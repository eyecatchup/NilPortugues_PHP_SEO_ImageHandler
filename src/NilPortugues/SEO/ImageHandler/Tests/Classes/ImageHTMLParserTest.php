<?php

namespace NilPortugues\SEO\ImageHandler\Tests\Classes;

class ImageHTMLParserTest extends \PHPUnit_Framework_TestCase
{
    protected $ImageHTMLParser;

    public function setUp()
    {
        $this->ImageHTMLParser = new \NilPortugues\SEO\ImageHandler\Classes\ImageHTMLParser();
    }

    public function testGetImagesFromHtmlWithImages()
    {
        $testCase = '<p>Some text blah blah blah <img style="float: left" src="image1.jpg"></p> <center><img src="image2.jpg"></center> <img src="image3.jpg"> <img src="image4.jpg">';
        $expected = array
        (
            '<img style="float: left" src="image1.jpg">',
            '<img src="image2.jpg">',
            '<img src="image3.jpg">',
            '<img src="image4.jpg">',
        );

        $result = $this->ImageHTMLParser->getImages($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, $expected);
    }

    public function testGetImagesFromHtmlWithoutImages()
    {
        $testCase = '<a href="http://google.com">Google</a>';
        $expected = array();

        $result = $this->ImageHTMLParser->getImages($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, $expected);
        $this->assertEmpty($result);
    }

    public function testGetImagesFromHtmlWithImagesInCommentCodeOnly()
    {
        $testCase = '<!-- <img src="image1.jpg"> -->';
        $expected = array();

        $result = $this->ImageHTMLParser->getImages($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, $expected);
        $this->assertEmpty($result);
    }

    public function testGetImagesFromHtmlWithImagesInCommentCode()
    {
        $testCase = '<!-- <img src="image1.jpg"> --><img src="image2.jpg">';
        $expected = array('<img src="image2.jpg">');

        $result = $this->ImageHTMLParser->getImages($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, $expected);
    }

    public function testGetImagesFromANonStringTypeParameter()
    {
        $testCase = array('this is not a string nor contains html');
        $expected = array();

        $result = $this->ImageHTMLParser->getImages($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($result, $expected);
        $this->assertEmpty($result);
    }

    public function testGetExistingAttrFromTag()
    {
        $testCase = '<img src="image.jpg" title="Image title">';
        $name = 'title';

        $result = $this->ImageHTMLParser->getAttrFromTag($name, $testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($result, 'Image title');
    }

    public function testGetExistingAttrFromEmptyTag()
    {
        $testCase = '</br>';
        $name = 'title';

        $result = $this->ImageHTMLParser->getAttrFromTag($name, $testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($result, '');
    }

    public function testGetNonExistentAttrFromTag()
    {
        $testCase = '<img src="image.jpg" title="Image title">';
        $name = 'alt';

        $result = $this->ImageHTMLParser->getAttrFromTag($name, $testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($result, '');
    }

    public function testGetNonExistentAttrFromEmptyTag()
    {
        $testCase = '';
        $name = 'title';
        $result = $this->ImageHTMLParser->getAttrFromTag($name, $testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($result, '');
    }

    public function testGetAttrFromEmptyData()
    {
        $testCase = '';
        $name = '';
        $result = $this->ImageHTMLParser->getAttrFromTag($name, $testCase);

        $this->assertInternalType('string', $result);
        $this->assertEquals($result, '');
    }


    public function testGetAllInlineCssHeightValuesFromTag()
    {
        $testCase = 'border: 1px solid red; min-height: 64px; max-height: 96px; height: 72px';
        $expected = array
        (
            'max' => '96px',
            'normal' => '72px',
            'min' => '64px'
        );


        $result = $this->ImageHTMLParser->getCssHeight($testCase);


        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetMinMaxInlineCssHeightValuesFromTag()
    {
        $testCase = 'min-height: 64 px; border: 1px solid red;  max-height: 96    px';
        $expected = array
        (
            'max' => '96px',
            'normal' => 0,
            'min' => '64px'
        );

        $result = $this->ImageHTMLParser->getCssHeight($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetMinNormalInlineCssHeightValuesFromTag()
    {
        $testCase = 'height: 72 px; ; border: 1px solid red; min-height: 64px; ';
        $expected = array
        (
            'max' => 0,
            'normal' => '72px',
            'min' => '64px'
        );

        $result = $this->ImageHTMLParser->getCssHeight($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetMaxNormalInlineCssHeightValuesFromTag()
    {
        $testCase = 'height: 72 px; ; border: 1px solid red; max-height: 96px; ';
        $expected = array
        (
            'max' => '96px',
            'normal' => '72px',
            'min' => 0
        );

        $result = $this->ImageHTMLParser->getCssHeight($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetInlineCssHeightValuesFromEmptyTag()
    {
        $testCase = '';
        $expected = array('max' => 0, 'normal' => 0, 'min' => 0);

        $result = $this->ImageHTMLParser->getCssHeight($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetAllInlineCssWidthValuesFromTag()
    {
        $testCase = 'border: 1px solid red; min-width: 64px; max-width: 96px; width: 72px';
        $expected = array
        (
            'max' => '96px',
            'normal' => '72px',
            'min' => '64px'
        );

        $result = $this->ImageHTMLParser->getCssWidth($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetMinMaxInlineCssWidthValuesFromTag()
    {
        $testCase = 'min-width: 64 px; border: 1px solid red;  max-width: 96    px';
        $expected = array
        (
            'max' => '96px',
            'normal' => 0,
            'min' => '64px'
        );

        $result = $this->ImageHTMLParser->getCssWidth($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetMinNormalInlineCssWidthValuesFromTag()
    {
        $testCase = 'width: 72 px; ; border: 1px solid red; min-width: 64px; ';
        $expected = array
        (
            'max' => 0,
            'normal' => '72px',
            'min' => '64px'
        );

        $result = $this->ImageHTMLParser->getCssWidth($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetMaxNormalInlineCssWidthValuesFromTag()
    {
        $testCase = 'width: 72 px; ; border: 1px solid red; max-width: 96px; ';
        $expected = array
        (
            'max' => '96px',
            'normal' => '72px',
            'min' => 0
        );

        $result = $this->ImageHTMLParser->getCssWidth($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testGetInlineCssWidthValuesFromEmptyTag()
    {
        $testCase = '';
        $expected = array('max' => 0, 'normal' => 0, 'min' => 0);

        $result = $this->ImageHTMLParser->getCssWidth($testCase);
        $this->assertInternalType('array', $result);
        $this->assertEquals($expected, $result);
    }

    public function testRemoveCssWidthFromTheEndOfTheStyleTag()
    {
        $testCase = 'border:1px solid red; min-height: 32            px; width: 72px;';
        $expected = 'border:1px solid red; min-height: 32 px';

        $result = $this->ImageHTMLParser->removeCssWidth($testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }

    public function testRemoveCssWidthFromTheStartOfTheStyleTag()
    {
        $testCase = 'width: 72  px; border:1px solid red; min-height: 32 px';
        $expected = 'border:1px solid red; min-height: 32 px';

        $result = $this->ImageHTMLParser->removeCssWidth($testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }

    public function testRemoveCssWidthFromTheMiddleOfTheStyleTag()
    {
        $testCase = 'border:1px solid red; width: 72  px;  min-height: 32 px';
        $expected = 'border:1px solid red; min-height: 32 px';

        $result = $this->ImageHTMLParser->removeCssWidth($testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }

    public function testRemoveCssWidthFromTheStyleTagWhenWidthNotInIt()
    {
        $testCase = 'border:1px solid red; min-width: 32 px';
        $expected = 'border:1px solid red; min-width: 32 px';

        $result = $this->ImageHTMLParser->removeCssWidth($testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }


    public function testRemoveCssHeightFromTheEndOfTheStyleTag()
    {
        $testCase = 'border:1px solid red; min-height: 32            px; height: 72px;';
        $expected = 'border:1px solid red; min-height: 32 px';

        $result = $this->ImageHTMLParser->removeCssHeight($testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }

    public function testRemoveCssHeightFromTheStartOfTheStyleTag()
    {
        $testCase = 'height: 72  px; border:1px solid red; min-height: 32 px';
        $expected = 'border:1px solid red; min-height: 32 px';

        $result = $this->ImageHTMLParser->removeCssHeight($testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }

    public function testRemoveCssHeightFromTheMiddleOfTheStyleTag()
    {
        $testCase = 'border:1px solid red; height: 72  px;  min-height: 32 px';
        $expected = 'border:1px solid red; min-height: 32 px';

        $result = $this->ImageHTMLParser->removeCssHeight($testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }


    public function testRemoveCssHeightFromTheStyleTagWhenHeightNotInIt()
    {
        $testCase = 'border:1px solid red; min-height: 32 px';
        $expected = 'border:1px solid red; min-height: 32 px';

        $result = $this->ImageHTMLParser->removeCssHeight($testCase);
        $this->assertInternalType('string', $result);
        $this->assertEquals($expected, $result);
    }


    public function tearDown()
    {
        $this->ImageHTMLParser = NULL;
    }
}
