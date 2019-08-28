<?php


namespace Okay\Core;


class DataCleaner
{
    
    private $db;
    private $config;
    
    public function __construct(Database $database, Config $config)
    {
        $this->db = $database;
        $this->config = $config;
    }
    
    public function clearCatalogData()
    {
        $this->db->customQuery("DELETE FROM `__comments` WHERE `type`='product'");
        $this->db->customQuery("UPDATE `__purchases` SET `product_id`=0, `variant_id`=0");
        $this->db->customQuery("TRUNCATE TABLE `__brands`");
        $this->db->customQuery("TRUNCATE TABLE `__categories`");
        $this->db->customQuery("TRUNCATE TABLE `__categories_features`");
        $this->db->customQuery("TRUNCATE TABLE `__features`");
        $this->db->customQuery("TRUNCATE TABLE `__features_aliases_values`");
        $this->db->customQuery("TRUNCATE TABLE `__features_values`");
        $this->db->customQuery("TRUNCATE TABLE `__images`");
        $this->db->customQuery("TRUNCATE TABLE `__import_log`");
        $this->db->customQuery("TRUNCATE TABLE `__lang_brands`");
        $this->db->customQuery("TRUNCATE TABLE `__lang_categories`");
        $this->db->customQuery("TRUNCATE TABLE `__lang_features`");
        $this->db->customQuery("TRUNCATE TABLE `__lang_features_aliases_values`");
        $this->db->customQuery("TRUNCATE TABLE `__lang_features_values`");
        $this->db->customQuery("TRUNCATE TABLE `__lang_products`");
        $this->db->customQuery("TRUNCATE TABLE `__lang_variants`");
        $this->db->customQuery("TRUNCATE TABLE `__options_aliases_values`");
        $this->db->customQuery("TRUNCATE TABLE `__products`");
        $this->db->customQuery("TRUNCATE TABLE `__products_categories`");
        $this->db->customQuery("TRUNCATE TABLE `__products_features_values`");
        $this->db->customQuery("TRUNCATE TABLE `__related_blogs`");
        $this->db->customQuery("TRUNCATE TABLE `__related_products`");
        $this->db->customQuery("TRUNCATE TABLE `__variants`");
    }

    public function clearResizeImages()
    {
        $this->clearFilesDirs($this->config->resized_images_dir);
        $this->clearFilesDirs($this->config->resized_blog_dir);
        $this->clearFilesDirs($this->config->resized_brands_dir);
        $this->clearFilesDirs($this->config->resized_categories_dir);
    }
    
    public function clearAllCatalogImages()
    {
        $this->clearFilesDirs($this->config->original_images_dir);
        $this->clearFilesDirs($this->config->resized_images_dir);

        $this->clearFilesDirs($this->config->original_brands_dir);
        $this->clearFilesDirs($this->config->resized_brands_dir);

        $this->clearFilesDirs($this->config->original_categories_dir);
        $this->clearFilesDirs($this->config->resized_categories_dir);

    }

    private function clearFilesDirs($dir = '')
    {
        if (empty($dir)) {
            return false;
        }
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != '.keep_folder' && $file != '.htaccess') {
                    @unlink($dir."/".$file);
                }
            }
            closedir($handle);
        }
    }
    
}