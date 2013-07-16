<?php
namespace NilPortugues\SEO\ImageHandler\Interfaces;

/**
 *
 */
interface ImageDataRecordInterface
{
    /**
     * @param  \NilPortugues\SEO\ImageHandler\Classes\ImageObject $obj
     * @return mixed
     */
    public function insertImageObject(\NilPortugues\SEO\ImageHandler\Classes\ImageObject $obj);

    /**
     * @param $hash
     * @return mixed
     */
    public function getImageByHash($hash);

    /**
     * @param  array $values
     * @return mixed
     */
    public function getExistingImageHashes($values = array());
}
