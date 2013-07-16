<?php
namespace NilPortugues\SEO\ImageHandler\Classes;

/**
 * A class that handles an array of ImageObject instances.
 */
class ImageObjectCollection
{
    protected $objectArray = array();

    /**
     *
     */
    public function __construct()
    {
        $this->objectArray = array();
    }

    /**
     * @return ImageObjectCollection
     */
    public function emptyCollection()
    {
        $this->objectArray = array();

        return $this;
    }

    /**
     * @return array
     */
    public function getCollection()
    {
        return $this->objectArray;
    }

    /**
     * @param $key
     * @return ImageObjectCollection
     */
    public function deleteObject($key)
    {
        if (!empty($this->objectArray[$key])) {
            unset($this->objectArray[$key]);
            return $this;
        }

        throw new \Exception("The $key key does not exist in the current instance of ImageObjectCollection.");
    }


    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function findObject($id)
    {
        if (!empty($this->objectArray[$id])) {
            return $this->objectArray[$id];
        }
        throw new \Exception("The is no ImageObject stored in the ImageObjectCollection in the $id position.");
    }

    /**
     * @param $currentKey
     * @param $newKey
     * @return ImageObjectCollection
     * @throws \Exception
     */
    public function setObjectKey($currentKey, $newKey)
    {
        if (!empty($this->objectArray[$currentKey])) {
            if ($currentKey != $newKey) {
                $this->objectArray[$newKey] = $this->objectArray[$currentKey];
                unset($this->objectArray[$currentKey]);
            }
            return $this;

        } else {
            throw new \Exception("The $currentKey key does not exist in the current instance of ImageObjectCollection.");
        }
    }

    /**
     * @param  ImageObject           $obj
     * @return ImageObjectCollection
     */
    public function addObject(\NilPortugues\SEO\ImageHandler\Classes\ImageObject $obj)
    {
        $id = $obj->getHash();
        if (empty($id)) {
            $this->objectArray[] = $obj;
        } else {
            if (!empty($this->objectArray[$id])) {
                $value = $obj->getImageTag();
                $this->objectArray[$id]->addOtherAppearances($value);
            } else {
                $this->objectArray[$id] = $obj;
            }
        }

        return $this;
    }
}
