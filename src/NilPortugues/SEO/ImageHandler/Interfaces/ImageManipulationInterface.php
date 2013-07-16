<?php
namespace NilPortugues\SEO\ImageHandler\Interfaces;

interface ImageManipulationInterface
{
    /**
     * @param $currentFilePath
     * @param $saveFilePath
     * @param $newWidth
     * @param $newHeight
     * @return mixed
     */
    public function resize($currentFilePath, $saveFilePath, $newWidth, $newHeight);
}
