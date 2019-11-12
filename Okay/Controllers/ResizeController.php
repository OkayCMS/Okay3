<?php


namespace Okay\Controllers;


use Okay\Core\Image;
use Okay\Helpers\ResizeHelper;

class ResizeController extends AbstractController
{
    
    public function resize(Image $image, ResizeHelper $resizeHelper, $object, $filename)
    {

        $filename = rawurldecode($filename);
        
        $originalImgDir = null;
        $resizedImgDir = null;
        $imageSizes = null;
        
        $resizeDirs = $resizeHelper->getResizeDirs($object);
        if (!empty($resizeDirs)) {
            list($originalImgDir, $resizedImgDir) = $resizeDirs;
        }
        
        if ($object == 'products') {
            $imageSizes = $this->settings->get('products_image_sizes');
        } else {
            $imageSizes = $this->settings->get('image_sizes');
        }
        
        if (empty($originalImgDir) && empty($resizedImgDir) && $object != 'products') {
            return false;
        }

        if (($resizedFilename = $image->resize($filename, $imageSizes, $originalImgDir, $resizedImgDir)) === false) {
            return false;
        }
        
        if (is_readable($resizedFilename)) {
            $this->response->setContent(file_get_contents($resizedFilename), RESPONSE_IMAGE);
        }
    }
    
}
