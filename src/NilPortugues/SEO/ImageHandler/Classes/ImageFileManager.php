<?php

namespace NilPortugues\SEO\ImageHandler\Classes;

/**
 * A class that handles all the necessary operations we'll be doing with images.
 */
class ImageFileManager
{
    /**
     * @param $path
     * @return bool
     */
    public function delete($path)
    {
        $path = realpath($path);

        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * @param $path
     * @return bool
     */
    public function existsDir($path)
    {
        $path = realpath($path);

        if (file_exists($path)) {
            return is_dir($path);
        }

        return false;
    }

    /**
     * @param $path
     * @param $file
     * @return bool
     */
    public function existsFile($path, $file)
    {
        $path = realpath($path);

        if ($this->existsDir($path)) {
            return file_exists($path . '/' . $file);
        }

        return false;
    }

    /**
     * @param $saveDir
     * @param $source
     * @return bool|string
     */
    public function downloadImage($saveDir, $source)
    {
        if (is_dir($saveDir)) {
            $binaryData = @file_get_contents($source);
            if ($binaryData === false) {
                return false;
            } else {
                $fileData = pathinfo($source);
                $fileName = $saveDir . '/' . $fileData['filename'] . '-' . time() . '.' . $fileData['extension'];
                file_put_contents($fileName, $binaryData);

                $hash = md5_file($fileName);
                $newFileName = $saveDir . '/' . $hash . '.' . $fileData['extension'];

                if (!file_exists($newFileName)) {
                    rename($fileName, $newFileName);
                } else {
                    unlink($fileName);
                }

                return $hash . '.' . $fileData['extension'];
            }
        }

        return false;
    }

    /**
     * Check if the path is an external URL or a local filesystem path.
     * This function does not actually check whether the file exists or not!!
     *
     * @param $path_url
     * @return bool
     * @throws \Exception
     */
    public function checkExternal($path_url)
    {
        //Case: http://mydomain.com/path/to/image.jpg OR http://externaldomain.com/path/to/image.jpg
        $url = parse_url($path_url);
        if (!empty($url['host'])) {

            $ourDomain = 'http://' . $_SERVER['HTTP_HOST'] . '/';
            $ourDomain = parse_url($ourDomain);
            $ourDomain = $ourDomain['host'];

            $url['host'] = strtolower($url['host']);

            if (strpos('www.', $url['host'])) {
                $url['host'] = str_replace('www.', '', $url['host']);
            }

            if ($url['host'] == $ourDomain) {
                //not external
                return false;
            }

            $haystack = $url['host'];
            $needle = $ourDomain;
            if (strlen($haystack) > strlen($needle)) {
                $pos = strpos($haystack, $needle);
            } else {
                $pos = strpos($needle, $haystack);
            }

            if ($pos === false) {
                //Looks external. Just make sure URL is valid.
                if (($path_url[0] == $path_url[1]) && ($path_url[0] == '/')) {
                    $path_url = 'http:' . $path_url;
                }

                if ((strpos($path_url, 'http://') !== false || strpos($path_url, 'https://') !== false) && filter_var($path_url, FILTER_VALIDATE_URL)) {
                    return true;
                } else {
                    throw new \Exception("Value $path_url is not a valid external URL.");
                }
            } else {
                //not external
                return false;
            }
        }

        //Local URL cases.
        $path_url = str_replace(array('http://', 'https://'), '', $path_url);

        //Case: //path/to/image.jpg
        if (($path_url[0] == $path_url[1]) && ($path_url[0] == '/')) {

            //not external
            return false;
        }

        //Case: /path/to/image.jpg
        if ($path_url[0] == '/') {
            //not external
            return false;
        }

        //Case: ./path/to/image.jpg
        if ($path_url[0] == '.' && $path_url[1] == '/') {
            //not external
            return false;
        }

        //Case: path/to/image.jpg
        $trailCount = substr_count($path_url, '/');
        $pathPartsCount = count(explode('/', $path_url));

        if ($trailCount == ($pathPartsCount - 1)) {
            return false;
        }

        //If still here, well, someone is really fucked up.
        throw new \Exception("Value $path_url is not a valid file path or URL.");
    }


    /**
     * Given an array of files, returns array with those rows pointing file paths that are local.
     *
     * @param  array $path_list
     * @return array
     */
    public function getExternalFiles(array $path_list)
    {
        foreach ($path_list as $key => $file) {

            try {
                if (!$this->checkExternal($file)) {
                    unset($path_list[$key]);
                }
            } catch (\Exception $e) {
                //Path entered is not valid, should be removed...
                unset($path_list[$key]);
            }
        }

        return $path_list;
    }

    /**
     * Builds a the list of files with the full file path location.
     *
     * @param $baseDir
     * @param  array      $file_list
     * @return array
     * @throws \Exception
     */
    public function getFullPath($baseDir, array $file_list)
    {
        if (file_exists($baseDir)) {
            foreach ($file_list as $key => $file) {
                $file = str_replace('//', '/', $baseDir . '/' . $file);
                $file_list[$key] = $file;
            }
        } else {
            throw new \Exception("Fatal error: $baseDir does not exist.");
        }

        return $file_list;
    }

    /**
     * Removes the filename from the path and returns the file path.
     *
     * @param $path
     * @return string
     */
    public function getPath($path)
    {
        $path = explode('/', $path);
        array_pop($path);
        $path = implode('/', $path);

        return $path;
    }

    /**
     * @param $fileName
     * @return string
     */
    public function normalizeLocalPath($path_url)
    {
        //Case: //path/to/image.jpg
        if (($path_url[0] == $path_url[1]) && ($path_url[0] == '/')) {

            $path_url = substr($path_url, 2);
        }

        //Case: /path/to/image.jpg
        if ($path_url[0] == '/') {

            $path_url = substr($path_url, 1);
        }

        //Case: ./path/to/image.jpg
        if ($path_url[0] == '.' && $path_url[1] == '/') {

            $path_url = substr($path_url, 2);
        }

        $path_url = str_replace('//', '/', $path_url);

        return $path_url;
    }


    /**
     * @param $realWidth
     * @param $realHeight
     * @param $currentWidth
     * @param $currentHeight
     * @return array
     */
    public function normalizeImageDimension($realWidth, $realHeight, $currentWidth, $currentHeight)
    {

        //WIDTH: Keep % or get the pixel value.
        if (strpos($currentWidth, '%') !== false) {
            $width = $currentWidth;
        } else {
            $width = $currentWidth;
            settype($width, 'integer');
        }

        //HEIGHT: Keep % or get the pixel value.
        if (strpos($currentHeight, '%') !== false) {
            $height = $currentHeight;
        } else {
            $height = $currentHeight;
            settype($height, 'integer');
        }

        //CALCULATE a DIMENSION when MISSING one of the values and unit is PIXELS.
        if (empty($width) && strpos($height, '%') === false) {
            $ratio = $realWidth / $realHeight;
            $width = ceil($ratio * $height);
        }

        if (empty($height) && strpos($width, '%') === false) {
            $ratio = $realHeight / $realWidth;
            $height = ceil($ratio * $width);
        }

        //Remove value for 0 pixels.
        if ($width == 0) {
            $width = '';
        }

        if ($height == 0) {
            $height = '';
        }

        return array('height' => $height, 'width' => $width);
    }
}
