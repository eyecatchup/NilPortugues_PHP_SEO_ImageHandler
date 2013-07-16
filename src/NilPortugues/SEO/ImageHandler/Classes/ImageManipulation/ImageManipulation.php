<?php
namespace NilPortugues\SEO\ImageHandler\Classes\ImageManipulation;

class ImageManipulation implements \NilPortugues\SEO\ImageHandler\Interfaces\ImageManipulationInterface
{
    /**
     * @param $currentFilePath
     * @param $saveFilePath
     * @param $newWidth
     * @param $newHeight
     * @return mixed
     */
    public function resize($currentFilePath, $saveFilePath, $newWidth, $newHeight)
    {
        $path_parts = pathinfo($saveFilePath);

        if (!empty($path_parts['dirname']) && !empty($path_parts['filename']) && !empty($path_parts['extension'])) {
            $image = new ImageClass();
            $image->setImage($currentFilePath)
                ->resize($newWidth, $newHeight, 'exact')
                ->save($path_parts['dirname'], $path_parts['filename'], $path_parts['extension']);
        } else {
            throw new \Exception("The value for \$saveFilePath: $saveFilePath, is not valid.");
        }
    }
}
