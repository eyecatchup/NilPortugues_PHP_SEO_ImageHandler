<?php
namespace NilPortugues\SEO\ImageHandler\Classes;

/**
 * A class that handles the images extracted by ImageParser.
 */
class ImageObject
{
    protected $data;

    /**
     *
     */
    public function __construct()
    {
        $this->data = array
        (
            'html' => '',
            'file_md5' => '',
            'filename' => '',
            'file_extension' => '',
            'filepath' => '',
            'width' => '',
            'height' => '',
            'title' => '',
            'alt' => '',
            'other_appearances' => array(),
            'parent_file_md5' => '',
        );
    }

    /**
     * @return mixed
     */
    public function getOtherAppearancesArray()
    {
        return $this->data['other_appearances'];
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function addOtherAppearances($value)
    {
        if (!empty($value)) {
            if (!in_array($value, $this->data['other_appearances'])) {
                $this->data['other_appearances'][] = $value;
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getImageTag()
    {
        return $this->data['html'];
    }

    /**
     * @return mixed
     */
    public function getAlt()
    {
        return $this->data['alt'];
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->data['title'];
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->data['filename'];
    }

    /**
     * @return mixed
     */
    public function getFileExtension()
    {
        return $this->data['file_extension'];
    }

    /**
     * @return mixed
     */
    public function getFilePath()
    {
        return $this->data['filepath'];
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->data['width'];
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->data['height'];
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->data['file_md5'];
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setImageTag($value)
    {
        $this->data['html'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setAlt($value)
    {
        $this->data['alt'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setTitle($value)
    {
        $this->data['title'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setFileName($value)
    {
        $this->data['filename'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setFileExtension($value)
    {
        $this->data['file_extension'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setFilePath($value)
    {
        $this->data['filepath'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setWidth($value)
    {
        $this->data['width'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setHeight($value)
    {
        $this->data['height'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setHash($value)
    {
        $this->data['file_md5'] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return ImageObject
     */
    public function setParentHash($value)
    {
        $this->data['parent_file_md5'] = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParentHash()
    {
        return $this->data['parent_file_md5'];
    }
}
