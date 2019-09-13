<?php


use Okay\Entities\ManagersEntity;
use Okay\Core\OkayContainer\OkayContainer;
use Okay\Entities\FeaturesValuesEntity;
use Okay\Core\QueryFactory;
use Okay\Logic\FeaturesLogic;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Core\EntityFactory;
use Okay\Entities\ProductsEntity;
use Okay\Entities\VariantsEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\ImagesEntity;
use Okay\Entities\BrandsEntity;
use Okay\Core\Languages;
use Okay\Core\Database;
use Okay\Core\Translit;
use Okay\Core\Request;
use Okay\Core\Managers;
use Okay\Core\Import;
use Okay\Core\Image;

require_once 'configure.php';

class ImportAjax
{
    /**
     * @var Database
     */
    protected $db;

    /**
     * @var Import
     */
    protected $import;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Translit
     */
    protected $translit;

    /**
     * @var Languages
     */
    protected $languages;

    /**
     * @var ImagesEntity
     */
    protected $imagesEntity;

    /**
     * @var BrandsEntity
     */
    protected $brandsEntity;

    /**
     * @var ProductsEntity
     */
    protected $productsEntity;

    /**
     * @var VariantsEntity
     */
    protected $variantsEntity;

    /**
     * @var CategoriesEntity
     */
    protected $categoriesEntity;

    /**
     * @var CurrenciesEntity
     */
    protected $currenciesEntity;

    /**
     * @var FeaturesValuesEntity
     */
    protected $featuresValuesEntity;

    /**
     * @var FeaturesEntity
     */
    protected $featuresEntity;

    /**
     * @var int
     */
    protected $productsCount = 100;

    /**
     * @var FeaturesLogic
     */
    protected $featuresLogic;

    /**
     * @var ManagersEntity
     */
    protected $managersEntity;

    /**
     * @var Managers
     */
    protected $managers;

    /**
     * @var QueryFactory
     */
    protected $queryBuilder;

    /**
     * @var Image
     */
    protected $imagesCore;

    public function __construct(OkayContainer $DI, EntityFactory $entityFactory)
    {
        $this->featuresValuesEntity = $entityFactory->get(FeaturesValuesEntity::class);
        $this->currenciesEntity     = $entityFactory->get(CurrenciesEntity::class);
        $this->categoriesEntity     = $entityFactory->get(CategoriesEntity::class);
        $this->managersEntity       = $entityFactory->get(ManagersEntity::class);
        $this->featuresEntity       = $entityFactory->get(FeaturesEntity::class);
        $this->productsEntity       = $entityFactory->get(ProductsEntity::class);
        $this->variantsEntity       = $entityFactory->get(VariantsEntity::class);
        $this->brandsEntity         = $entityFactory->get(BrandsEntity::class);
        $this->imagesEntity         = $entityFactory->get(ImagesEntity::class);
        $this->queryBuilder         = $DI->get(QueryFactory::class);
        $this->featuresLogic        = $DI->get(FeaturesLogic::class);
        $this->languages            = $DI->get(Languages::class);
        $this->db                   = $DI->get(Database::class);
        $this->translit             = $DI->get(Translit::class);
        $this->request              = $DI->get(Request::class);
        $this->managers             = $DI->get(Managers::class);
        $this->import               = $DI->get(Import::class);
        $this->imagesCore           = $DI->get(Image::class);
    }

    public function import()
    {
        if (!$this->managers->access('import', $this->managersEntity->get($_SESSION['admin']))) {
            return false;
        }

        $fields = $_SESSION['csv_fields'];
        session_write_close();
        unset($_SESSION['lang_id']);
        unset($_SESSION['admin_lang_id']);
        
        // Для корректной работы установим локаль UTF-8
        setlocale(LC_ALL, $this->import->getLocale());
        
        $result = new \stdClass();
        
        // Определяем колонки из первой строки файла
        $f = fopen($this->import->getImportFilesDir() . $this->import->getImportFile(), 'r');
        $this->import->setColumns(fgetcsv($f, null, $this->import->getColumnDelimiter()));
        $this->import->initInternalColumns($fields);

        // Если нет названия товара - не будем импортировать
        if (empty($fields) || !in_array('sku', $fields) && !in_array('name', $fields)) {
            return false;
        }

        // Переходим на заданную позицию, если импортируем не сначала
        if($from = $this->request->get('from')) {
            fseek($f, $from);
        } else {
            $sql = $this->queryBuilder->newSqlQuery()->setStatement("TRUNCATE __import_log");
            $this->db->query($sql);
        }

        // Массив импортированных товаров
        $importedItems = array();

        // Проходимся по строкам, пока не конец файла
        // или пока не импортировано достаточно строк для одного запроса
        for($k=0; !feof($f) && $k < $this->productsCount; $k++) {
            // Читаем строку
            $line = fgetcsv($f, 0, $this->import->getColumnDelimiter());

            $product = null;
            if(is_array($line) && !empty($line)) {
                $i = 0;
                // Проходимся по колонкам строки
                foreach ($fields as $csv=>$inner) {
                    // Создаем массив item[название_колонки]=значение
                    if (isset($line[$i]) && !empty($inner)) {
                        $product[$inner] = $line[$i];
                    }
                    $i++;
                }
            }

            // Импортируем этот товар
            if($importedItem = $this->importItem($product)) {
                $importedItems[] = $importedItem;
            }
        }
        
        // Запоминаем на каком месте закончили импорт
        $from = ftell($f);
        
        // И закончили ли полностью весь файл
        $result->end = feof($f);
        
        fclose($f);
        $size = filesize($this->import->getImportFilesDir() . $this->import->getImportFile());
        
        // Создаем объект результата
        $result->from = $from;          // На каком месте остановились
        $result->totalsize = $size;     // Размер всего файла
        
        foreach ($importedItems as $item) {
            $productId    = (int)$item->product->id;
            $status       = (string)$item->status;
            $productName  = (string)$item->product->name;
            $variantName  = (string)$item->variant->name;

            $insert = $this->queryBuilder->newInsert();
            $insert->into('__import_log')
                ->cols([
                    'product_id' => $productId,
                    'status' => $status,
                    'product_name' => $productName,
                    'variant_name' => $variantName,
                ]);
            $this->db->query($insert);
        }
        
        return $result;
    }
    
    // Импорт одного товара $item[column_name] = value;
    private function importItem($item)
    {
        $importedItem = new \stdClass();

        // Проверим не пустое ли название и артинкул (должно быть хоть что-то из них)
        if (empty($item['sku']) && empty($item['name'])) {
            return false;
        }

        // Подготовим товар для добавления в базу
        $product = array();
        
        if (isset($item['name'])) {
            $product['name'] = trim($item['name']);
        }
        
        if (isset($item['meta_title'])) {
            $product['meta_title'] = trim($item['meta_title']);
        }
        
        if (isset($item['meta_keywords'])) {
            $product['meta_keywords'] = trim($item['meta_keywords']);
        }
        
        if (isset($item['meta_description'])) {
            $product['meta_description'] = trim($item['meta_description']);
        }
        
        if (isset($item['annotation'])) {
            $product['annotation'] = trim($item['annotation']);
        }
        else {
            $product['annotation'] = '';
        }
        
        if (isset($item['description'])) {
            $product['description'] = trim($item['description']);
        }
        else {
            $product['description'] = '';
        }
        
        if (isset($item['visible'])) {
            $product['visible'] = intval($item['visible']);
        }
        
        if (isset($item['featured'])) {
            $product['featured'] = intval($item['featured']);
        }
        
        if (!empty($item['url'])) {
            $product['url'] = $this->translit->translit(trim($item['url']));
        }

        // Если задан бренд
        if (!empty($item['brand'])) {
            $item['brand'] = trim($item['brand']);
            // Найдем его по имени
            if ($this->languages->getLangId()) {
                $select = $this->queryBuilder->newSelect();
                $select->cols(['brand_id as id'])
                    ->from('__lang_brands')
                    ->where('name=:name')
                    ->where('lang_id=:lang_id')
                    ->bindValue('name', $item['brand'])
                    ->bindValue('lang_id', $this->languages->getLangId());
                $this->db->query($select);
            } else {
                $select = $this->queryBuilder->newSelect();
                $select->cols(['id'])
                    ->from('__brands')
                    ->where('name=:name')
                    ->bindValue('name', $item['brand']);
                $this->db->query($select);
            }
            if (!$product['brand_id'] = $this->db->result('id')) {
                // Создадим, если не найден
                $product['brand_id'] = $this->brandsEntity->add(array(
                    'name'             => $item['brand'], 
                    'meta_title'       => $item['brand'], 
                    'meta_keywords'    => $item['brand'], 
                    'meta_description' => $item['brand']));
            }
        }

        // Если задана категория
        $categoryId = null;
        $categories_ids = array();
        if (!empty($item['category'])) {
            foreach (explode($this->import->getCategoryDelimiter(), $item['category']) as $c) {
                $categories_ids[] = $this->importCategory($c);
            }
            $categoryId = reset($categories_ids);
        }
        
        // Подготовим вариант товара
        $variant = array();
        $variant['price'] = 0;
        $variant['compare_price'] = 0;
        
        if (isset($item['variant'])) {
            $variant['name'] = trim($item['variant']);
        }

        if (isset($item['price']) && !empty($item['price'])) {
            $variant['price'] = str_replace(',', '.', str_replace(' ', '', trim($item['price'])));
        }
        
        if (isset($item['compare_price']) && !empty($item['compare_price'])) {
            $variant['compare_price'] = str_replace(',', '.', str_replace(' ', '', trim($item['compare_price'])));
        }

        if (isset($item['stock'])) {
            if ($item['stock'] == '') {
                $variant['stock'] = null;
            } else {
                $variant['stock'] = trim($item['stock']);
            }
        }
        
        if (isset($item['sku'])) {
            $variant['sku'] = trim($item['sku']);
        }
        
        if (isset($item['currency'])) {
            $variant['currency_id'] = intval($item['currency']);
        }
        if (isset($item['weight'])) {
            $variant['weight'] = (float)str_replace(',', '.', str_replace(' ', '', trim($item['weight'])));
        }

        if (isset($item['units'])) {
            $variant['units'] = $item['units'];
        }

        // Если задан артикул варианта, найдем этот вариант и соответствующий товар
        if (!empty($variant['sku'])) {
            $select = $this->queryBuilder->newSelect();
            $select->cols(['v.id as variant_id', 'v.product_id'])
                ->from('__variants v')
                ->where('v.sku=:sku')
                ->bindValue('sku', $variant['sku']);

            if (!empty($variant['name'])) {
                $select->where('v.name=:name')
                    ->bindValue('name', $variant['name']);
            }
            $this->db->query($select);

            $result = $this->db->result();

            if ($result) {
                $productId = $result->product_id;
                $variantId = $result->variant_id;
                $importedItem->status = 'updated';
            } 

            elseif (!empty($product['name'])) {
                $select = $this->queryBuilder->newSelect();
                $select->cols(['p.id as product_id'])
                    ->from('__products p')
                    ->where('p.name=:name')
                    ->limit(1)
                    ->bindValue('name', $product['name']);
                $this->db->query($select);

                $result = $this->db->result();
                if ($result) {
                    $productId = $result->product_id;
                    $importedItem->status = 'added';
                } 
                else {
                    $importedItem->status = 'added';
                }
            }
        } else {
            // если нет артикула попробуем по названию товара
            $select = $this->queryBuilder->newSelect();
            $select->cols(['v.id as variant_id', 'p.id as product_id'])
                ->from('__products p')
                ->join('LEFT', '__variants v', 'v.product_id=p.id')
                ->where('p.name=:p_name')
                ->bindValue('p_name', $product['name']);
                if (!empty($variant['name'])) {
                    $select->where('v.name=:v_name')
                        ->bindValue('v_name', $variant['name']);
                }
            $this->db->query($select);
            $result = $this->db->result();

            if ($result) {
                $productId = $result->product_id;
                $variantId = $result->variant_id;
                if (empty($variantId)) {
                    $importedItem->status = 'added';
                } else {
                    $importedItem->status = 'updated';
                    unset($variant['sku']);
                }
            } else {
                $importedItem->status = 'added';
            }
        }

        if (isset($importedItem->status)) {
            if (!empty($product)) {
                if (!isset($product['url']) && !empty($product['name']) && empty($result->url)) {
                    $product['url'] = $this->translit->translit($product['name']);
                }

                if (empty($productId)) {
                    
                    // Если для нового товара не заданы метаданные, запишем туда название товара
                    if (!isset($product['meta_title']) || empty($product['meta_title'])) {
                        $product['meta_title'] = $product['name'];
                    }

                    if (!isset($product['meta_keywords']) || empty($product['meta_keywords'])) {
                        $product['meta_keywords'] = $product['name'];
                    }

                    if (!isset($product['meta_description']) || empty($product['meta_description'])) {
                        $product['meta_description'] = $product['name'];
                    }
                    $productId = $this->productsEntity->add($product);
                } else {
                    $this->productsEntity->update($productId, $product);
                }
            }

            if (empty($variantId) && !empty($productId)) {
                $select = $this->queryBuilder->newSelect();
                $select->cols(['MAX(v.position) as pos'])
                    ->from('__variants v')
                    ->where('v.product_id=:product_id')
                    ->limit(1)
                    ->bindValue('product_id', $productId);
                $this->db->query($select);
                $pos = $this->db->result('pos');

                $variant['position'] = $pos+1;
                $variant['product_id'] = $productId;
                if (!isset($variant['currency_id'])) {
                    $currency = $this->currenciesEntity->getMainCurrency();
                    $variant['currency_id'] = $currency->id;
                }

                // нужно хотяб одно поле из переводов
                $fields = $this->variantsEntity->getFields();
                $tm = array_intersect(array_keys($variant), $fields);
                if (empty($tm) && !empty($fields)) {
                    $variant[$fields[0]] = "";
                }

                $variantId = $this->variantsEntity->add($variant);
            } elseif (!empty($variantId) && !empty($variant)) {
                $this->variantsEntity->update($variantId, $variant);
            }
        }

        if(!empty($variantId) && !empty($productId)) {
            // Нужно вернуть обновленный товар
            $importedItem->variant = $this->variantsEntity->get(intval($variantId));
            $importedItem->product = $this->productsEntity->get(intval($productId));

            // Добавляем категории к товару
            $select = $this->queryBuilder->newSelect();
            $select->cols(['MAX(position) as pos'])
                ->from('__products_categories')
                ->where('product_id=:product_id')
                ->bindValue('product_id', $productId);
            $this->db->query($select);
            $pos = $this->db->result('pos');

            $pos = $pos === false ? 0 : $pos + 1;
            if (!empty($categories_ids)) {
                foreach ($categories_ids as $c_id) {
                    $this->categoriesEntity->addProductCategory($productId, $c_id, $pos++);
                }
            }

            // Изображения товаров
            $imagesIds = array();
            if (isset($item['images'])) {
                // Изображений может быть несколько, через запятую
                $images = explode(',', $item['images']);
                foreach ($images as $image) {
                    $image = trim($image);
                    if (!empty($image)) {
                        // Имя файла
                        $imageFilename = pathinfo($image, PATHINFO_BASENAME);

                        if (preg_match("~^https?://~", $image)) {
                            $imageFilename = $this->imagesCore->correctFilename($imageFilename);
                            $image = rawurlencode($image);
                        }

                        // Добавляем изображение только если такого еще нет в этом товаре
                        $select = $this->queryBuilder->newSelect();
                        $select->cols(['id', 'filename'])
                            ->from('__images')
                            ->where('product_id=:product_id')
                            ->where('(filename=:image_filename OR filename=:image)')
                            ->limit(1)
                            ->bindValue('product_id', $productId)
                            ->bindValue('image_filename', $imageFilename)
                            ->bindValue('image', $image);
                        $this->db->query($select);
                        $result = $this->db->result();

                        if (empty($result->filename)) {
                            $newImage = new stdClass();
                            $newImage->product_id = $productId;
                            $newImage->filename = $image;
                            $imagesIds[] = $this->imagesEntity->add($newImage);
                        } else {
                            $imagesIds[] = $result->id;
                        }
                    }
                }
            }

            $mainInfo = array();
            $mainImage = reset($imagesIds);
            if (!empty($mainImage)) {
                $mainInfo['main_image_id'] = $mainImage;
            }

            if (!empty($categoryId)) {
                $mainInfo['main_category_id'] = $categoryId;
            }

            if (!empty($mainInfo)) {
                $this->productsEntity->update($productId, $mainInfo);
            }

            // Характеристики товара
            $features = [];
            foreach ($item as $featureName => $featureValue) {
                if ($this->isFeature($featureName)) {
                    $features[$featureName] = $featureValue;
                }
            }

            if (!empty($features)) {
                $this->featuresLogic->addFeatures($features, $productId, $categoryId);
            }

            return $importedItem;
        }
    }

    private function isFeature($importColumnName)
    {
        if (!in_array($importColumnName, $this->import->getInternalColumnsNames())) {
            return true;
        }

        return false;
    }
    
    // Отдельная функция для импорта категории
    private function importCategory($category)
    {
        // Поле "категория" может состоять из нескольких имен, разделенных subcategory_delimiter-ом
        // Только неэкранированный subcategory_delimiter может разделять категории
        $delimiter = $this->import->getSubcategoryDelimiter();
        $regex = "/\\DELIMITER((?:[^\\\\\DELIMITER]|\\\\.)*)/";
        $regex = str_replace('DELIMITER', $delimiter, $regex);
        $names = preg_split($regex, $category, 0, PREG_SPLIT_DELIM_CAPTURE);
        $id = null;
        $parent = 0;
        
        // Для каждой категории
        foreach($names as $name) {
            // Заменяем \/ на /
            $name = trim(str_replace("\\$delimiter", $delimiter, $name));
            if(!empty($name)) {
                // Найдем категорию по имени
                $select = $this->queryBuilder->newSelect();
                $select->cols(['id'])
                    ->from('__categories')
                    ->where('name=:name')
                    ->where('parent_id=:parent')
                    ->bindValue('name', $name)
                    ->bindValue('parent', $parent);
                $this->db->query($select);
                $id = $this->db->result('id');
                
                // Если не найдена - добавим ее
                if(empty($id)) {
                    $id = $this->categoriesEntity->add([
                        'name'             => $name, 
                        'parent_id'        => $parent, 
                        'meta_title'       => $name,  
                        'meta_keywords'    => $name,  
                        'meta_description' => $name, 
                        'url'              => $this->translit->translit($name),
                    ]);
                }
                
                $parent = $id;
            }
        }
        return $id;
    }
    
}

$importAjax = new ImportAjax($DI, $entityFactory);
$data = $importAjax->import();
if ($data) {
    $response->setContent(json_encode($data), RESPONSE_JSON)->sendContent();
}
