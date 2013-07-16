<?php
namespace NilPortugues\SEO\ImageHandler\Tests\Classes;

class ImageFileManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $ImageFileManager;

    public function setUp()
    {
        $this->ImageFileManager = new \NilPortugues\SEO\ImageHandler\Classes\ImageFileManager();
    }

    public function testRelativeDirectoryExists()
    {
        $path = 'src/NilPortugues/SEO/ImageHandler/Classes';
        $result = $this->ImageFileManager->existsDir($path);
        $this->assertInternalType('boolean', $result);
        $this->assertTrue($result);
    }

    public function testRelativeDirectoryDoesNotExists()
    {
        $path = 'this/directory/does/not/exist';
        $result = $this->ImageFileManager->existsDir($path);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testAbsoluteDirectoryExists()
    {
        $path = getcwd();
        $result = $this->ImageFileManager->existsDir($path);
        $this->assertInternalType('boolean', $result);
        $this->assertTrue($result);
    }

    public function testAbsoluteDirectoryDoesNotExists()
    {
        $path = '/this/directory/does/not/exist';
        $result = $this->ImageFileManager->existsDir($path);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testRelativePathFileExists()
    {
        $path = 'src/NilPortugues/SEO/ImageHandler/Classes';
        $file = 'ImageFileManager.php';
        $result = $this->ImageFileManager->existsFile($path, $file);
        $this->assertInternalType('boolean', $result);
        $this->assertTrue($result);
    }

    public function testRelativePathFileDoesNotExists()
    {
        $path = 'this/directory/does/not/exist';
        $file = 'i-do-not-exist.php';
        $result = $this->ImageFileManager->existsFile($path, $file);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testAbsolutePathFileExists()
    {
        $path = getcwd() . '/' . 'src/NilPortugues/SEO/ImageHandler/Classes';
        $file = 'ImageFileManager.php';
        $result = $this->ImageFileManager->existsFile($path, $file);
        $this->assertInternalType('boolean', $result);
        $this->assertTrue($result);
    }

    public function testAbsolutePathFileDoesNotExists()
    {
        $path = '/this/directory/does/not/exist';
        $file = 'i-do-not-exist.php';
        $result = $this->ImageFileManager->existsFile($path, $file);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testUrlIsExternalPath()
    {
        $_SERVER['HTTP_HOST'] = 'mydomain.com';
        $path_url = 'http://google.es/image.jpg';
        $result = $this->ImageFileManager->checkExternal($path_url);
        $this->assertInternalType('boolean', $result);
        $this->assertTrue($result);
        unset($_SERVER['HTTP_HOST']);
    }

    public function testUrlIsExternalPathButBelongsToOurDomain()
    {
        $_SERVER['HTTP_HOST'] = 'mydomain.com';
        $path_url = 'http://static.mydomain.com/image.jpg';
        $result = $this->ImageFileManager->checkExternal($path_url);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
        unset($_SERVER['HTTP_HOST']);
    }

    public function testAbsoluteUrlIsExternalPath()
    {
        $path_url = '/path/to/image.jpg';
        $result = $this->ImageFileManager->checkExternal($path_url);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testAbsoluteDoubleTrailUrlIsExternalPath()
    {
        $path_url = '//path/to/image.jpg';
        $result = $this->ImageFileManager->checkExternal($path_url);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testRelativeDotUrlIsExternalPath()
    {
        $path_url = './path/to/image.jpg';
        $result = $this->ImageFileManager->checkExternal($path_url);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testRelativeUrlIsExternalPath()
    {
        $path_url = 'path/to/image.jpg';
        $result = $this->ImageFileManager->checkExternal($path_url);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testExternalPathException()
    {
        $this->setExpectedException('Exception');
        $this->ImageFileManager->checkExternal('smb://static.mydomain.com/image.jpg');
    }

    public function testRelativeDotNormalizeLocalPath()
    {
        $testCase = './path/to/image.jpg';
        $expected = 'path/to/image.jpg';
        $result = $this->ImageFileManager->normalizeLocalPath($testCase);
        $this->assertEquals($result, $expected);
    }

    public function testAbsoluteDoubleTrailNormalizeLocalPath()
    {
        $testCase = '//path/to/image.jpg';
        $expected = 'path/to/image.jpg';
        $result = $this->ImageFileManager->normalizeLocalPath($testCase);
        $this->assertEquals($result, $expected);
    }

    public function testAbsoluteTrailNormalizeLocalPath()
    {
        $testCase = '/path/to/image.jpg';
        $expected = 'path/to/image.jpg';
        $result = $this->ImageFileManager->normalizeLocalPath($testCase);
        $this->assertEquals($result, $expected);
    }

    public function testRelativeNormalizeLocalPath()
    {
        $testCase = 'path/to/image.jpg';
        $expected = 'path/to/image.jpg';
        $result = $this->ImageFileManager->normalizeLocalPath($testCase);
        $this->assertEquals($result, $expected);
    }

    public function testGetPath()
    {
        $testCase = 'path/to/image.jpg';
        $expected = 'path/to';

        $result = $this->ImageFileManager->getPath($testCase);
        $this->assertEquals($result, $expected);
    }

    public function testGetExternalFilesImagesHavingEmptyArray()
    {
        $_SERVER['HTTP_HOST'] = 'mydomain.com';
        $testCase = array();
        $expected = array();

        $result = $this->ImageFileManager->getExternalFiles($testCase);
        $this->assertEquals($result, $expected);
        $this->assertCount(0, $result);
        unset($_SERVER['HTTP_HOST']);
    }


    public function testGetExternalFilesImagesHavingLocalDataInArray()
    {
        $_SERVER['HTTP_HOST'] = 'mydomain.com';
        $testCase = array
        (
            'http://mydomain.com/image1.jpg',
            '//path/to/image2.jpg',
            '/path/to/image3.jpg',
            './path/to/image4.jpg',
            'path/to/image5.jpg',
        );
        $expected = array();

        $result = $this->ImageFileManager->getExternalFiles($testCase);
        $this->assertEquals($result, $expected);
        $this->assertCount(0, $result);
        unset($_SERVER['HTTP_HOST']);
    }

    public function testGetExternalFilesImagesHavingExternalDataInArray()
    {
        $_SERVER['HTTP_HOST'] = 'mydomain.com';
        $testCase = array
        (
            'http://google.es/image.jpg',
            'http://mydomain.com/image1.jpg',
            '//path/to/image2.jpg',
            '/path/to/image3.jpg',
            './path/to/image4.jpg',
            'path/to/image5.jpg',
        );
        $expected = array
        (
            'http://google.es/image.jpg',
        );

        $result = $this->ImageFileManager->getExternalFiles($testCase);
        $this->assertEquals($expected, $result);
        $this->assertContainsOnly('string', $result);
        unset($_SERVER['HTTP_HOST']);
    }

    public function testDownloadExistingImageToExistentDirectory()
    {
        $saveDir = 'images/downloaded';
        $source = 'http://www.google.com/images/google_favicon_128.png';
        $expected = 'adc3f992294e3ad5137756a00901abfc.png';

        $result = $this->ImageFileManager->downloadImage($saveDir, $source);
        $this->assertEquals($result, $expected);

        $filePath = $saveDir . '/' . $result;
        $this->assertFileExists($filePath);
    }

    public function testDownloadExistingImageToNonExistentDirectory()
    {
        $saveDir = 'i/dont/exist';
        $source = 'http://www.google.com/images/google_favicon_128.png';

        $result = $this->ImageFileManager->downloadImage($saveDir, $source);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testDownloadNonExistentImageToExistentDirectory()
    {
        $saveDir = 'images/downloaded';
        $source = 'http://www.google.com/images/google_favicon_XXX.png';

        $result = $this->ImageFileManager->downloadImage($saveDir, $source);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testDownloadNonExistentImageToNonExistentDirectory()
    {
        $saveDir = 'i/dont/exist';
        $source = 'http://www.google.com/images/google_favicon_XXX.png';

        $result = $this->ImageFileManager->downloadImage($saveDir, $source);
        $this->assertInternalType('boolean', $result);
        $this->assertFalse($result);
    }

    public function testNormalizeImageDimensionsWithPercentageWidth()
    {
        $realWidth = '500';
        $realHeight = '500';
        $currentWidth = '25%';
        $currentHeight = '';

        $expectedHeight = '';
        $expectedWidth = '25%';

        $result = $this->ImageFileManager->normalizeImageDimension($realWidth, $realHeight, $currentWidth, $currentHeight);

        $this->assertEquals($expectedHeight, $result['height']);
        $this->assertEquals($expectedWidth, $result['width']);
    }

    public function testNormalizeImageDimensionsWithPercentageWidthOver100()
    {
        $realWidth = '500';
        $realHeight = '500';
        $currentWidth = '125%';
        $currentHeight = '';

        $expectedWidth = '125%';
        $expectedHeight = '';


        $result = $this->ImageFileManager->normalizeImageDimension($realWidth, $realHeight, $currentWidth, $currentHeight);

        $this->assertEquals($expectedHeight, $result['height']);
        $this->assertEquals($expectedWidth, $result['width']);
    }

    public function testNormalizeImageDimensionsWithPercentageHeight()
    {
        $realWidth = '500';
        $realHeight = '500';

        $currentWidth = '';
        $currentHeight = '25%';

        $expectedWidth = '';
        $expectedHeight = '25%';


        $result = $this->ImageFileManager->normalizeImageDimension($realWidth, $realHeight, $currentWidth, $currentHeight);

        $this->assertEquals($expectedHeight, $result['height']);
        $this->assertEquals($expectedWidth, $result['width']);
    }

    public function testNormalizeImageDimensionsWithPercentageWidthAndHeight()
    {
        $realWidth = '500';
        $realHeight = '500';
        $currentWidth = '1%';
        $currentHeight = '99%';

        $expectedWidth = '1%';
        $expectedHeight = '99%';

        $result = $this->ImageFileManager->normalizeImageDimension($realWidth, $realHeight, $currentWidth, $currentHeight);

        $this->assertEquals($expectedHeight, $result['height']);
        $this->assertEquals($expectedWidth, $result['width']);
    }

    public function testNormalizeImageDimensionsWithPixelWidthAndHeight()
    {
        $realWidth = '400';
        $realHeight = '240';
        $currentWidth = '300px';
        $currentHeight = '600px';

        $expectedWidth = 300;
        $expectedHeight = 600;

        $result = $this->ImageFileManager->normalizeImageDimension($realWidth, $realHeight, $currentWidth, $currentHeight);

        $this->assertEquals($expectedHeight, $result['height']);
        $this->assertEquals($expectedWidth, $result['width']);
    }

    public function testGetExternalFilesImagesHavingExternalDataInArray2()
    {
        $_SERVER['HTTP_HOST'] = 'mydomain.com';
        $testCase = array
        (
            'http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg',
            'images/m2x.png',
        );
        $expected = array
        (
            'http://www.pokemonxy.com/_ui/img/_en/screenshots/june_new_p11_01.jpg',
        );

        $result = $this->ImageFileManager->getExternalFiles($testCase);
        $this->assertEquals($expected, $result);
        $this->assertContainsOnly('string', $result);
        unset($_SERVER['HTTP_HOST']);
    }

    public function tearDown()
    {
        $this->ImageFileManager = NULL;
    }
}
