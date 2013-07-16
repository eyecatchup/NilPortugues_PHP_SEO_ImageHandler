<?php
namespace NilPortugues\SEO\ImageHandler;

/**
 * The main class.
 */
class ImageHandler
{
    protected $databaseObject;
    protected $htmlParser;
    protected $fileManager;
    protected $imageObject;
    protected $imageObjectCollection;

    /**
     * @param Classes\ImageHTMLParser             $ihp
     * @param Classes\ImageFileManager            $ifm
     * @param Classes\ImageObject                 $io
     * @param Classes\ImageObjectCollection       $ioc
     * @param Interfaces\ImageDataRecordInterface $db
     */
    public function __construct(
        \NilPortugues\SEO\ImageHandler\Classes\ImageHTMLParser $ihp,
        \NilPortugues\SEO\ImageHandler\Classes\ImageFileManager $ifm,
        \NilPortugues\SEO\ImageHandler\Classes\ImageObject $io,
        \NilPortugues\SEO\ImageHandler\Classes\ImageObjectCollection $ioc,
        \NilPortugues\SEO\ImageHandler\Interfaces\ImageDataRecordInterface $db
    )
    {
        $this->htmlParser = $ihp;
        $this->imageObjectCollection = $ioc;
        $this->fileManager = $ifm;
        $this->imageObject = $io;
        $this->databaseObject = $db;
    }

    /**
     * Adds and Image to the image record system and returns the recorded data for further usage.
     *
     * @param $imgSrc
     * @param $baseDir
     * @param $imageDir
     * @param $imageDomain
     * @return mixed
     */
    public function addImage($imgSrc, $baseDir, $imageDir, $imageDomain)
    {
        // Build a <img> tag for $imgSrc
        $html = '<img src="' . $imgSrc . '">';

        // Save Images in the data structure and record it to the system if new
        $imageCollection = $this->saveImages($html, $baseDir, $imageDir, $imageDomain);

        // Return data.
        $imageObjectArray = $imageCollection->getCollection();
        if (!empty($imageObjectArray)) {
            $imageObject = array_shift($imageObjectArray);
            if (!empty($imageObject)) {
                $hash = $imageObject->getHash();
                $result = $this->databaseObject->getExistingImageHashes(array($hash));

                if (!empty($result[0])) {
                    return $result[0];
                }
            }
        }
        return false;
    }

    /**
     * Returns HTML code with all the <img> tags replaced by custom {{IMG}} tags.
     *
     * @param $html
     * @param $baseDir
     * @param $imageDir
     * @return mixed
     */
    public function getParsedHtml($html, $baseDir, $imageDir)
    {
        // Save Images in the data structure and record it to the system if new
        $imageCollection = $this->saveImages($html, $baseDir, $imageDir);

        $commentData = $this->replaceCommentsForPlaceholders($html);
        $html = $this->replaceImgTagForCustomTag($commentData['html'], $imageCollection);
        $html = $this->replacePlaceholdersForComments($html, $commentData['placeholders']);

        return $html;
    }

    /**
     * Returns HTML code replacing {{IMG}} tags replaced by <img> tags again.
     *
     * @param $html
     * @param $imageDomain
     * @return mixed
     */
    public function getHtml($html, $imageDomain)
    {
        //Temporally remove all comments and replace them with placeholders.
        $commentData = $this->replaceCommentsForPlaceholders($html);
        $html = $commentData['html'];

        //First, delete all <img> tags. If there were any around these are certainly broken images or unauthorized.
        $html = preg_replace('#(<img.*?>)#', '', $html);

        //Secondly, replace custom {{IMG}} tags for <img>
        preg_match_all('#{{IMG\|(.*?)}}#', $html, $results);
        if (!empty($results[1])) {
            $data = array();

            //Read all values from the custom tags
            $hashList = array();
            foreach ($results[1] as $v) {
                $t = explode('|', $v);
                $hash = $t[0];
                array_shift($t);

                $hashList[] = $hash;
                $data[$v] = $t;
            }

            //Fetch all hash values
            $imageArray = $this->databaseObject->getExistingImageHashes($hashList);
            if (!empty($imageArray)) {
                $imageDomain = str_replace(array('http://', 'https://'), '', $imageDomain);

                //Build an array holding data and key being the hash.
                $newImageArray = array();
                foreach ($imageArray as $row) {
                    $newImageArray[$row['file_md5']] = $row;
                }
                $imageArray = $newImageArray;
                unset($newImageArray);

                //Replace {{IMG}} for <img>.
                foreach ($data as $customTag => $row) {
                    $temp = explode('|', $customTag);
                    $hash = array_shift($temp);
                    $originalAttr = implode('|', $temp);

                    //Preserve the extra original <img> attributes.
                    if (!empty($originalAttr)) {
                        $originalAttr = '|' . $originalAttr;
                    } else {
                        $originalAttr = '';
                    }

                    //Add the required attributes.
                    $image = $imageArray[$hash];
                    $imgAttr = array();
                    $imgAttr['src'] = '//' . str_replace('//', '/', $imageDomain . '/' . $image['filepath']);
                    $imgAttr['alt'] = $image['alt'];
                    $imgAttr['title'] = $image['title'];
                    $imgAttr['width'] = $image['width'];
                    $imgAttr['height'] = $image['height'];

                    //Remove $imgAttr['width'] if width is defined in inline CSS.
                    $style = strtolower($this->htmlParser->getAttrFromTag('style', $originalAttr));
                    if (strpos($style, 'width') !== false || strpos($style, 'min-width') !== false || strpos($style, 'max-width') !== false) {
                        unset($imgAttr['width']);
                    }

                    //Remove $imgAttr['height'] if height is defined in inline CSS.
                    if (strpos($style, 'height') !== false || strpos($style, 'min-height') !== false || strpos($style, 'max-height') !== false) {
                        unset($imgAttr['height']);
                    }

                    //Do not set height if width is % AND attr width is not set.
                    $w = $this->htmlParser->getCssWidth($style);
                    if (strpos($w['normal'], '%') !== false && empty($imgAttr['width'])) {
                        unset($imgAttr['height']);
                    }

                    //Do not set width if height is %.
                    $h = $this->htmlParser->getCssHeight($style);
                    if (strpos($h['normal'], '%') !== false && empty($imgAttr['height'])) {
                        unset($imgAttr['width']);
                    }

                    //Remove empty fields.
                    $imgAttr = array_filter($imgAttr);

                    //Rebuild the image attributes.
                    $imgData = array();
                    foreach ($imgAttr as $attrName => $attrValue) {
                        $imgData[$attrName] = $attrName . '="' . $attrValue . '"';
                    }

                    //Rebuild the image tag.
                    $customTag = '{{IMG|' . $customTag . '}}';
                    $imageTag = '<img ' . implode(' ', $imgData) . ' ' . strtolower(implode(' ', explode('|', $originalAttr))) . '>';
                    $imageTag = preg_replace('/\s+/', ' ', $imageTag);
                    $html = str_replace($customTag, $imageTag, $html);
                }
            }

            // Remove any remaining image custom tags.
            // This can only happen if someone edited the image database manually and deleted data.
            $html = preg_replace('#{{IMG\|(.*?)}}#', '', $html);
        }

        return $this->replacePlaceholdersForComments($html, $commentData['placeholders']);
    }


    /**
     * This function replaces HTML comment tags to placeholders in order to guarantee
     * we won't be processing commented images.
     *
     * @param $html
     * @return array
     */
    protected function replaceCommentsForPlaceholders($html)
    {
        $commentList = array();
        preg_match_all('#<!--(.*)-->#Uis', $html, $comments);
        if (!empty($comments)) {
            foreach ($comments[1] as $k => $value) {
                $newKey = '#' . base64_encode("[COMMENT_BLOCK_NUMBER_$k]") . '#';
                $html = preg_replace('#<!--' . $value . '-->#Uis', $newKey, $html);
                $commentList[$newKey] = '<!--' . $value . '-->';
            }
        }

        return array('html' => $html, 'placeholders' => $commentList);
    }

    /**
     * This function replaces comment placeholders to HTML comment tags.
     *
     * @param $html
     * @param array $comments
     */
    protected function replacePlaceholdersForComments($html, array $comments)
    {
        // Restore comments
        if (!empty($comments)) {
            $keys = array_keys($comments);
            $values = array_values($comments);
            $html = str_replace($keys, $values, $html);
        }

        return $html;
    }


    /**
     * Loops through all collected images and replaces the <img> tags to {{IMG}} tags.
     *
     * @param $html
     * @param  Classes\ImageObjectCollection $imageCollection
     * @return mixed
     */
    protected function replaceImgTagForCustomTag($html, \NilPortugues\SEO\ImageHandler\Classes\ImageObjectCollection $imageCollection)
    {
        $imageObjectArray = $imageCollection->getCollection();

        foreach ($imageObjectArray as $object) {
            //Replace the first recorded appearance of the tag
            $html = $this->replaceCustomTag($html, $object->getImageTag(), $object->getHash());

            //Replace all the other image appearances
            $other = $object->getOtherAppearancesArray();
            if (!empty($other)) {
                foreach ($other as $imgTag) {
                    $html = $this->replaceCustomTag($html, $imgTag, $object->getHash());
                }
            }
        }

        return $html;
    }

    /**
     * Replaces <img> for {{IMG}} keeping all the html attributes.
     *
     * @param $html
     * @param $imageTag
     * @param $imageHash
     * @return mixed
     */
    protected function replaceCustomTag($html, $imageTag, $imageHash)
    {
        $attr = array();
        $attr['id'] = $this->htmlParser->getAttrFromTag('id', $imageTag);
        $attr['class'] = $this->htmlParser->getAttrFromTag('class', $imageTag);
        $attr['style'] = $this->htmlParser->getAttrFromTag('style', $imageTag);

        //Remove css Width if value is not %
        $w = $this->htmlParser->getCssWidth($attr['style']);
        if (!empty($w['normal']) && strpos($w['normal'], '%') === false) {
            $attr['style'] = $this->htmlParser->removeCssWidth($attr['style']);
        }

        //Remove css height if value is not %
        $h = $this->htmlParser->getCssHeight($attr['style']);
        if (!empty($h['normal']) && strpos($h['normal'], '%') === false) {
            $attr['style'] = $this->htmlParser->removeCssHeight($attr['style']);
        }

        //If width is % and height value is set in pixels in IMG attr, add height pixel value to style attr.
        $attr_height = $this->htmlParser->getAttrFromTag('height', $imageTag);
        if (strpos($w['normal'], '%') !== false && !empty($attr_height) && strpos($attr_height, '%') === false) {
            settype($attr_height, 'integer');
            $attr['style'] = 'height: ' . $attr_height . 'px; ' . $attr['style'];
        }

        //If height is % and width value is set in pixels in IMG attr, add width pixel value to style attr.
        $attr_width = $this->htmlParser->getAttrFromTag('width', $imageTag);
        if (strpos($h['normal'], '%') !== false && !empty($attr_width) && strpos($attr_width, '%') === false) {
            settype($attr_width, 'integer');
            $attr['style'] = 'width: ' . $attr_width . 'px; ' . $attr['style'];
        }

        //Keep the attributes with a with a value only
        foreach ($attr as $attrName => $attrValue) {
            if ($attrValue == NULL) {
                unset($attr[$attrName]);
            }
        }

        //Build the {{IMG}} attribute list
        $attrList = array();
        foreach ($attr as $attrName => $attrValue) {
            $attrList[] = $attrName . '="' . $attrValue . '"';
        }

        //Build the {{IMG}} data-* attributes list
        preg_match_all('#data-(.*?)="(.*?)"#', $imageTag, $dataAttr);
        if (count($dataAttr) > 0 && count($dataAttr[0]) > 0) {
            $attrList = array_merge($attrList, $dataAttr[0]);
        }

        //Build the image attribute for later reconstruction.
        if (count($attrList) > 0) {
            $attrList = '|' . implode('|', $attrList);
        } else {
            $attrList = '';
        }

        //Replace the image with our new tag.
        $customTag = '{{IMG|' . $imageHash . $attrList . '}}';
        $customTag = preg_replace('/\s+/', ' ', $customTag);
        $html = str_replace($imageTag, $customTag, $html);


        return $html;
    }

    /**
     * Method parses HTML, downloads external images to $imageDir, being a folder inside the base directory defined by $baseDir.
     * All downloaded images and its data is recorded for future reference.
     *
     * @param $html
     * @param $baseDir
     * @param $imageDir
     * @return Classes\ImageObjectCollection
     * @throws \Exception
     */
    protected function saveImages($html, $baseDir, $imageDir)
    {
        if (!$this->fileManager->existsDir($baseDir)) {
            throw new \Exception("The specified $baseDir directory does not exist.");
        }

        if (!$this->fileManager->existsDir($baseDir . '/' . $imageDir)) {
            throw new \Exception("The specified $baseDir.'/'.$imageDir directory does not exist.");
        }

        //STEP 1 - GET ALL IMAGE  FILE PATHS
        $imageTags = $this->htmlParser->getImages($html);
        $imageFilePaths = array();
        foreach ($imageTags as $imgTag) {
            $imageFilePaths[$imgTag] = $this->htmlParser->getAttrFromTag('src', $imgTag);
        }

        //STEP 2 - GET IMAGE LIST OF THOSE IMAGES THAT ARE EXTERNAL
        $imagesExternalTags = array_keys($this->fileManager->getExternalFiles($imageFilePaths));

        //STEP 3 - GET IMAGE LIST OF THOSE IMAGES THAT ARE EXTERNAL
        $imagesLocalTags = array_diff($imageTags, $imagesExternalTags);

        //Process Image Tags
        $this->imageObjectCollection->emptyCollection();
        if (!empty($imagesLocalTags)) {
            $this->addImageTagsToImageCollection($imagesLocalTags, false, $baseDir, $imageDir);
        }

        if (!empty($imagesExternalTags)) {
            $this->addImageTagsToImageCollection($imagesExternalTags, true, $baseDir, $imageDir);
        }

        //Calculate ImageObjectCollection hashes and all the additional data for all the Objects.
        $this->imageObjectCollectionDataCalculation($baseDir);

        //Persist those ImageObjectCollection Objects that do not exist in our record and ignore those who do.
        $this->imageObjectCollectionPersist();

        //Return images to be used by getNewHtml()
        return $this->imageObjectCollection;
    }

    /**
     * @param $images
     * @param bool   $external
     * @param string $saveDir
     * @param string $imageDir
     */
    protected function addImageTagsToImageCollection($images, $external = false, $saveDir = '', $imageDir = '')
    {
        foreach ($images as $img) {
            $imageNotFound = false;

            if ($external) {
                $filePath = $this->htmlParser->getAttrFromTag('src', $img);
                $fileName = $this->fileManager->downloadImage($saveDir . '/' . $imageDir, $filePath);

                if ($fileName === false) {
                    $imageNotFound = true;
                } else {
                    $filePath = $imageDir . '/' . $fileName;
                    $filePath = str_replace('//', '/', $filePath);
                    $filePath = $this->fileManager->normalizeLocalPath($filePath);
                }
            } else {
                $filePath = $this->htmlParser->getAttrFromTag('src', $img);
                $filePath = $this->fileManager->normalizeLocalPath($filePath);


                if ($this->fileManager->existsFile($saveDir, $filePath) == false) {
                    $imageNotFound = true;
                } else {
                    $filePath = str_replace('//', '/', $filePath);
                }
            }

            if (!$imageNotFound) {
                $alt = $this->htmlParser->getAttrFromTag('alt', $img);
                $title = $this->htmlParser->getAttrFromTag('title', $img);
                $style = $this->htmlParser->getAttrFromTag('style', $img);

                $css_height = $this->htmlParser->getCssHeight($style);
                $css_width = $this->htmlParser->getCssWidth($style);
                $attr_height = $this->htmlParser->getAttrFromTag('height', $img);
                $attr_width = $this->htmlParser->getAttrFromTag('width', $img);

                if (!empty($css_height['normal'])) {
                    $height = $css_height['normal'];
                } else {
                    $height = $attr_height;
                }

                if (!empty($css_width['normal'])) {
                    $width = $css_width['normal'];
                } else {
                    $width = $attr_width;
                }

                //create instance and add Image to Collection
                $instance = get_class($this->imageObject);
                $object = new $instance();
                $object
                    ->setImageTag($img)
                    ->setFilePath($filePath)
                    ->setAlt($alt)
                    ->setTitle($title)
                    ->setHeight($height)
                    ->setWidth($width);

                $this->imageObjectCollection->addObject($object);
            }
        }
    }


    /**
     * @param $baseDir
     */
    protected function imageObjectCollectionDataCalculation($baseDir)
    {
        $image = new \NilPortugues\SEO\ImageHandler\Classes\ImageManipulation\ImageManipulation();
        $imageObjectArray = $this->imageObjectCollection->getCollection();

        //Calculate all the NEW images and Create them.
        $extraImageObjects = array();
        foreach ($imageObjectArray as $imageObject) {
            $filePath = $imageObject->getFilePath();
            $md5_path = $baseDir . '/' . $filePath;
            list($width, $height) = getimagesize($baseDir . '/' . $filePath);
            $path_parts = pathinfo($filePath);

            //Normalize height and width to pixels.
            $dimensions = $this->fileManager->normalizeImageDimension($width, $height, $imageObject->getWidth(), $imageObject->getHeight());

            //If it's an image to be resized
            if (
                !empty($dimensions['height'])
                && !empty($dimensions['width'])
                && $dimensions['height'] > 0
                && $dimensions['width'] > 0
                && $dimensions['height'] != $height
                && $dimensions['width'] != $width
            ) {
                $newFilePath = $path_parts['dirname'] . '/' . $path_parts['filename'] . '.' . $dimensions['width'] . 'x' . $dimensions['height'] . '.' . $path_parts['extension'];

                if ($this->fileManager->existsFile('.', $newFilePath) == false) {
                    $image->resize($filePath, $newFilePath, $dimensions['width'], $dimensions['height']);
                }

                $parentHash = md5_file($md5_path);
                $hash = md5_file($baseDir . '/' . $newFilePath);

                $imageObject
                    ->setImageTag($imageObject->getImageTag())
                    ->setFilePath($newFilePath)
                    ->setAlt($imageObject->getAlt())
                    ->setTitle($imageObject->getTitle())
                    ->setHeight($dimensions['height'])
                    ->setWidth($dimensions['width'])
                    ->setParentHash($parentHash)
                    ->setHash($hash)
                    ->setFileExtension($path_parts['extension'])
                    ->setFileName($path_parts['filename'] . '.' . $dimensions['width'] . 'x' . $dimensions['height']);
                $extraImageObjects[] = $imageObject;
            } //Else, leave image untouched
            else {
                $hash = md5_file($md5_path);

                $imageObject
                    ->setHash($hash)
                    ->setFileExtension($path_parts['extension'])
                    ->setFileName($path_parts['filename'])
                    ->setWidth($width)
                    ->setHeight($height);
                $extraImageObjects[] = $imageObject;
            }
        }

        //Empty collection and add all images
        $this->imageObjectCollection->emptyCollection();
        foreach ($extraImageObjects as $object) {
            $this->imageObjectCollection->addObject($object);
        }
    }

    /**
     *
     */
    protected function imageObjectCollectionPersist()
    {
        $imageObjectArray = $this->imageObjectCollection->getCollection();

        $hashList = array();
        foreach ($imageObjectArray as $imageObject) {
            $hashList[] = $imageObject->getHash();
        }

        $imageObjectArrayCopy = $imageObjectArray;
        $doNotPersistList = $this->databaseObject->getExistingImageHashes($hashList);
        if (!empty($doNotPersistList)) {
            foreach ($doNotPersistList as $row) {
                try {
                    $object = $this->imageObjectCollection->findObject($row['file_md5']);
                } catch (\Exception $e) {
                    $object = NULL;
                }

                if (!empty($object)) {

                    //Remove the object from the array.
                    unset($imageObjectArrayCopy[$row['file_md5']]);
                }
            }
        }

        //Insert first those having no parent hash
        foreach ($imageObjectArrayCopy as $k => $imageObject) {
            $parentHash = $imageObject->getParentHash();
            if (in_array($imageObject->getHash(), $doNotPersistList) == false && empty($parentHash)) {
                $this->databaseObject->insertImageObject($imageObject);
                unset($imageObjectArrayCopy[$k]);
            }
        }

        //Later insert all those having a parent hash
        foreach ($imageObjectArrayCopy as $imageObject) {
            $parentHash = $imageObject->getParentHash();
            if (in_array($imageObject->getHash(), $doNotPersistList) == false && !empty($parentHash)) {
                $this->databaseObject->insertImageObject($imageObject);
            }
        }

    }
}
