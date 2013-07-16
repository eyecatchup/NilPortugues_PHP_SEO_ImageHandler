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
        $url = parse_url($path_url);
        if (empty($url['host'])) {
            $url = array('host' => '');
        }

        if (!empty($url['scheme']) && $url['scheme'] != 'http' && $url['scheme'] != 'https') {
            throw new \Exception("Value " . $url['scheme'] . " is not a valid scheme.");
        }

        $cases = array
        (
            //Not external patterns
            '/' => ($path_url[0] == '/' && $path_url[1] != '/') ? true : false,
            './' => ($path_url[0] == '.' && $path_url[1] == '/') ? true : false,

            //Could be neither
            '//' =>
            (
                $path_url[0] == $path_url[1]
                    && $path_url[0] == '/'
                    && filter_var('http:' . $path_url, FILTER_VALIDATE_URL) != false
            ) ? true : false,

        );

        if ($cases['/']) {
            //is not external
            return false;
        } elseif ($cases['./']) {
            //is not external
            return false;
        } else {
            //Try comparing the domain names.
            $ourDomain = parse_url('http://' . $_SERVER['HTTP_HOST'] . '/');
            $ourDomain = strtolower($ourDomain['host']);
            $externalDomain = strtolower($url['host']);


            if (strpos($ourDomain, 'www.') !== false) {
                $ourDomain = str_replace('www.', '', $ourDomain);
            }

            if (strpos($externalDomain, 'www.') !== false) {
                $externalDomain = str_replace('www.', '', $externalDomain);
            }

            //Case: //external.com/path/to/image instead of http://external.com/path/to/image,
            if ($cases['//']) {
                if ($ourDomain != $externalDomain) {
                    return false;
                } else {
                    return true;
                }
            } else {
                $haystack = $externalDomain;
                $needle = $ourDomain;

                if (!empty($needle) && !empty($haystack)) {
                    if (strlen($haystack) > strlen($needle)) {
                        $pos = strpos($haystack, $needle);
                    } else {
                        $pos = strpos($needle, $haystack);
                    }

                    if ($pos === false) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    //Case: path/to/image.jpg
                    $trailCount = substr_count($path_url, '/');
                    $pathPartsCount = count(explode('/', $path_url));

                    if ($trailCount == ($pathPartsCount - 1)) {
                        return false;
                    }
                }
            }
        }
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
                if ($this->checkExternal($file) === false) {
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
