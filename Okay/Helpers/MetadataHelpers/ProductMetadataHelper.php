<?php


namespace Okay\Helpers\MetadataHelpers;


use Okay\Core\Modules\Extender\ExtenderFacade;

class ProductMetadataHelper extends CommonMetadataHelper
{
    
    public function getH1()
    {
        $product  = $this->design->getVar('product');
        $h1 = $product->name;

        if (count($product->variants) == 1 && !empty($product->variant->name)) {
            $h1 .= ' ' . $product->variant->name;
        }
        $h1 = $this->compileMetadata($h1);
        return ExtenderFacade::execute(__METHOD__, $h1, func_get_args());
    }
    
    public function getDescription()
    {
        $category = $this->design->getVar('category');
        $product  = $this->design->getVar('product');
        $defaultProductsSeoPattern = (object)$this->settings->get('default_products_seo_pattern');
        $description = $product->description;
        if (empty($description)) {
            if (!empty($category) && !empty($category->auto_description)) {
                $description = $category->auto_description;
            } elseif (!empty($defaultProductsSeoPattern->auto_description)) {
                $description = $defaultProductsSeoPattern->auto_description;
            }
        }
        $description = $this->compileMetadata($description);
        return ExtenderFacade::execute(__METHOD__, $description, func_get_args());
    } 
    
    public function getMetaTitle()
    {
        $category = $this->design->getVar('category');
        $product  = $this->design->getVar('product');
        $defaultProductsSeoPattern = (object)$this->settings->get('default_products_seo_pattern');
        
        if (!empty($category) && !empty($category->auto_meta_title)) {
            $autoMetaTitle = $category->auto_meta_title;
        } elseif (!empty($defaultProductsSeoPattern->auto_meta_title)) {
            $autoMetaTitle = $defaultProductsSeoPattern->auto_meta_title;
        } else {
            $autoMetaTitle = $product->meta_title;
        }
        $metaTitle = $this->compileMetadata($autoMetaTitle);
        return ExtenderFacade::execute(__METHOD__, $metaTitle, func_get_args());
    }
    
    public function getMetaKeywords()
    {
        $category = $this->design->getVar('category');
        $product  = $this->design->getVar('product');
        $defaultProductsSeoPattern = (object)$this->settings->get('default_products_seo_pattern');
        
        if (!empty($category) && !empty($category->auto_meta_keywords)) {
            $autoMetaKeywords = $category->auto_meta_keywords;
        } elseif (!empty($defaultProductsSeoPattern->auto_meta_keywords)) {
            $autoMetaKeywords = $defaultProductsSeoPattern->auto_meta_keywords;
        } else {
            $autoMetaKeywords = $product->meta_keywords;
        }
        $metaKeywords = $this->compileMetadata($autoMetaKeywords);
        return ExtenderFacade::execute(__METHOD__, $metaKeywords, func_get_args());
    }
    
    public function getMetaDescription()
    {
        $category = $this->design->getVar('category');
        $product  = $this->design->getVar('product');
        $defaultProductsSeoPattern = (object)$this->settings->get('default_products_seo_pattern');
        
        if (!empty($category) && !empty($category->auto_meta_desc)) {
            $autoMetaDescription = $category->auto_meta_desc;
        } elseif (!empty($defaultProductsSeoPattern->auto_meta_desc)) {
            $autoMetaDescription = $defaultProductsSeoPattern->auto_meta_desc;
        } else {
            $autoMetaDescription = $product->meta_description;
        }
        $metaDescription = $this->compileMetadata($autoMetaDescription);
        return ExtenderFacade::execute(__METHOD__, $metaDescription, func_get_args());
    }

    /**
     * Метод возвращает массив переменных и их значений, который учавствуют в формировании метаданных
     * @return array
     */
    protected function getParts()
    {
        if (!empty($this->parts)) {
            return $this->parts; // no ExtenderFacade
        }
        
        $currency = $this->mainHelper->getCurrentCurrency();
        $product = $this->design->getVar('product');

        $this->parts = [
            '{$brand}'         => ($this->design->getVar('brand') ? $this->design->getVar('brand')->name : ''),
            '{$product}'       => ($this->design->getVar('product') ? $this->design->getVar('product')->name : ''),
            '{$price}'         => ($product->variant->price != null ? $this->money->convert($product->variant->price, $currency->id, false) . ' ' . $currency->sign : ''),
            '{$compare_price}' => ($product->variant->compare_price != null ? $this->money->convert($product->variant->compare_price, $currency->id, false) . ' ' . $currency->sign : ''),
            '{$sku}'           => ($product->variant->sku != null ? $product->variant->sku : ''),
            '{$sitename}'      => ($this->settings->get('site_name') ? $this->settings->get('site_name') : '')
        ];

        if ($category = $this->design->getVar('category')) {
            $this->parts['{$category}'] = ($category->name ? $category->name : '');
            $this->parts['{$category_h1}'] = ($category->name_h1 ? $category->name_h1 : '');

            if (!empty($product->features)) {
                foreach ($product->features as $feature) {
                    if ($feature->auto_name_id) {
                        $this->parts['{$' . $feature->auto_name_id . '}'] = $feature->name;
                    }
                    if ($feature->auto_value_id) {
                        $this->parts['{$' . $feature->auto_value_id . '}'] = $feature->stingify_values;
                    }
                }
            }
        }
        return $this->parts = ExtenderFacade::execute(__METHOD__, $this->parts, func_get_args());
    }
    
}