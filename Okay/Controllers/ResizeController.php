<?php


namespace Okay\Controllers;


use Okay\Core\Image;

class ResizeController extends AbstractController
{
    
    public function resize(Image $image, $object, $filename)
    {

        $filename = rawurldecode($filename);
        
        $originalImgDir = null;
        $resizedImgDir = null;
        $imageSizes = null;
        if (!empty($object)) {
            //$object - по сути папка с нарезанными картинками
            if ($object == 'products') {
                $originalImgDir = $this->config->get('original_images_dir');
                $resizedImgDir = $this->config->get('resized_images_dir');
            }
            if ($object == 'blog') {
                $originalImgDir = $this->config->get('original_blog_dir');
                $resizedImgDir = $this->config->get('resized_blog_dir');
            }
            if ($object == 'brands') {
                $originalImgDir = $this->config->get('original_brands_dir');
                $resizedImgDir = $this->config->get('resized_brands_dir');
            }
            if ($object == 'categories') {
                $originalImgDir = $this->config->get('original_categories_dir');
                $resizedImgDir = $this->config->get('resized_categories_dir');
            }
            if ($object == 'deliveries') {
                $originalImgDir = $this->config->get('original_deliveries_dir');
                $resizedImgDir = $this->config->get('resized_deliveries_dir');
            }
            if ($object == 'payments') {
                $originalImgDir = $this->config->get('original_payments_dir');
                $resizedImgDir = $this->config->get('resized_payments_dir');
            }
            if ($object == 'lang') {
                $originalImgDir = $this->config->get('lang_images_dir');
                $resizedImgDir = $this->config->get('lang_resized_dir');
            }
            $extendsResizeObjects = $image->getResizeObjects();
            
            // Проверим может кто расширил директории ресайзов
            if (isset($extendsResizeObjects[$object])) {
                $originalImgDir = $extendsResizeObjects[$object]['original_dir'];
                $resizedImgDir  = $extendsResizeObjects[$object]['resized_dir'];
            }
        }
        
        if ($object == 'products') {
            $imageSizes = $this->settings->get('products_image_sizes');
        } else {
            $imageSizes = $this->settings->get('image_sizes');
        }
        
        if (empty($originalImgDir) && empty($resizedImgDir) && $object != 'products') {
            $this->response->setStatusCode(404)->sendHeaders();
            return;
        }

        $resizedFilename = $image->resize($filename, $imageSizes, $originalImgDir, $resizedImgDir);
        if (is_readable($resizedFilename)) {
            $this->response->setContent(file_get_contents($resizedFilename), RESPONSE_IMAGE);
        }
    }
    
}
