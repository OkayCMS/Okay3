<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;
use Okay\Core\Image;

class BannersImagesEntity extends Entity
{
    
    protected static $fields = [
        'id',
        'banner_id',
        'image',
        'position',
        'visible',
    ];

    protected static $langFields = [
        'name',
        'alt',
        'title',
        'description',
        'url',
    ];

    protected static $defaultOrderFields = [
        'position DESC',
    ];

    protected static $table = '__banners_images';
    protected static $langObject = 'banner_image';
    protected static $langTable = 'banners_images';
    protected static $tableAlias = 'bi';

    public function delete($ids)
    {
        if (empty($ids)) {
            return false;
        }

        /** @var Image $imageCore */
        $imageCore = $this->serviceLocator->getService(Image::class);

        $ids = (array)$ids;
        foreach ($ids as $id) {
            $imageCore->deleteImage(
                (int)$id,
                'image',
                self::class,
                $this->config->banners_images_dir,
                $this->config->resized_banners_images_dir
            );
        }

        return parent::delete($ids);
    }
    
}
