<?php


namespace Okay\Core;


use Okay\Entities\VariantsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\ImagesEntity;

class WishList
{
    /** @var ProductsEntity */
    private $productsEntity;

    /** @var VariantsEntity */
    private $variantsEntity;

    /** @var ImagesEntity */
    private $imagesEntity;

    private $settings;

    public function __construct(
        EntityFactory $entityFactory,
        Settings $settings
    ){
        $this->productsEntity = $entityFactory->get(ProductsEntity::class);
        $this->variantsEntity = $entityFactory->get(VariantsEntity::class);
        $this->imagesEntity   = $entityFactory->get(ImagesEntity::class);
        $this->settings       = $settings;
    }

    public function get()
    {
        $wishList = new \stdClass();
        $wishList->products = [];
        $wishList->ids = [];

        $items = !empty($_COOKIE['wishlist']) ? unserialize($_COOKIE['wishlist']) : [];
        if (!empty($items) && is_array($items)) {
            $products = [];
            $images_ids = [];
            foreach ($this->productsEntity->find(['id'=>$items, 'visible'=>1]) as $p) {
                $products[$p->id] = $p;
                $images_ids[] = $p->main_image_id;
            }
            if (!empty($products)) {
                $products_ids = array_keys($products);
                $wishList->ids = $products_ids;
                foreach($products as $product) {
                    $product->variants = [];
                }

                $variants = $this->variantsEntity->find(array('product_id'=>$products_ids));

                foreach($variants as $variant) {
                    $products[$variant->product_id]->variants[] = $variant;
                }

                if (!empty($images_ids)) {
                    $images = $this->imagesEntity->find(array('id'=>$images_ids));
                    foreach ($images as $image) {
                        if (isset($products[$image->product_id])) {
                            $products[$image->product_id]->image = $image;
                        }
                    }
                }

                
                foreach($products as $product) {
                    if(isset($product->variants[0])) {
                        $product->variant = $product->variants[0];
                    }
                }
                $wishList->products = $products;
            }
        }
        return $wishList;
    }

    public function addItem($productId)
    {
        $items = !empty($_COOKIE['wishlist']) ? unserialize($_COOKIE['wishlist']) : array();
        $items = $items && is_array($items) ? $items : array();
        if (!in_array($productId, $items)) {
            $items[] = $productId;
        }
        $_COOKIE['wishlist'] = serialize($items);
        setcookie('wishlist', $_COOKIE['wishlist'], time()+30*24*3600, '/');
    }

    /*Удаление товара из корзины*/
    public function deleteItem($productId)
    {
        $items = !empty($_COOKIE['wishlist']) ? unserialize($_COOKIE['wishlist']) : array();
        if (!is_array($items)) {
            return;
        }
        $i = array_search($productId, $items);
        if ($i !== false) {
            unset($items[$i]);
        }
        $_COOKIE['wishlist'] = serialize($items);
        setcookie('wishlist', $_COOKIE['wishlist'], time()+30*24*3600, '/');
    }
    
    /*Очистка списка сравнения*/
    public function emptyWishList()
    {
        unset($_COOKIE['wishlist']);
        setcookie('wishlist', '', time()-3600, '/');
    }
}
