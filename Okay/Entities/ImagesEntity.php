<?php


namespace Okay\Entities;


use Okay\Core\Entity\Entity;



class ImagesEntity extends Entity
{
    protected static $fields = [
        'id',
        'name',
        'product_id',
        'filename',
        'position'
    ];

    protected static $langFields = [];

    protected static $searchFields = [];

    protected static $defaultOrderFields = [
        'product_id',
        'position',
    ];

    protected static $table = '__images';
    protected static $langObject = 'image';
    protected static $tableAlias = 'i';
    
    public function delete($ids)
    {
        foreach ((array)$ids as $id) {
            
            if ($image = $this->get((int)$id)) {
                $filename = $image->filename;
                
                parent::delete($id);
                if ($this->count(['filename' => $filename]) == 0) {
                    $file = pathinfo($filename, PATHINFO_FILENAME);
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);

                    // Удалить все ресайзы
                    $rezisedImages = glob($this->config->root_dir.$this->config->resized_images_dir.$file.".*x*.".$ext);
                    if(is_array($rezisedImages)) {
                        foreach ($rezisedImages as $f) {
                            @unlink($f);
                        }
                    }

                    @unlink($this->config->root_dir.$this->config->original_images_dir.$filename);
                }
            }
        }
    }

}