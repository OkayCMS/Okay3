<?php


namespace Okay\Controllers;


use Okay\Core\Money;
use Okay\Core\Notify;
use Okay\Core\Validator;
use Okay\Entities\CommentsEntity;
use Okay\Entities\ProductsEntity;
use Okay\Entities\BrandsEntity;
use Okay\Entities\CategoriesEntity;
use Okay\Entities\BlogEntity;
use Okay\Logic\ProductsLogic;

class ProductController extends AbstractController
{

    /*Отображение товара*/
    public function render(
        ProductsEntity $productsEntity,
        BrandsEntity $brandsEntity,
        CategoriesEntity $categoriesEntity,
        ProductsLogic $productsLogic,
        Money $moneyCore,
        CommentsEntity $commentsEntity,
        BlogEntity $blogEntity,
        Validator $validate,
        Notify $notify,
        $url
    ) {
        
        if (empty($url)) {
            return false;
        }
        
        // Выбираем товар из базы
        $product = $productsEntity->get((string)$url);
        if (empty($product) || (!$product->visible && empty($_SESSION['admin']))) {
            return false;
        }
        
        //lastModify
        $this->response->setHeaderLastModify($product->last_modify);

        $product = $productsLogic->attachProductData($product);
        
        // Вариант по умолчанию
        if (($vId = $this->request->get('variant', 'integer'))>0 && isset($product->variants[$vId])) {
            $product->variant = $product->variants[$vId];
        } else {
            $product->variant = reset($product->variants);
        }

        // Автозаполнение имени для формы комментария
        if (!empty($this->user)) {
            $this->design->assign('comment_name', $this->user->name);
            $this->design->assign('comment_email', $this->user->email);
        }
        
        // Принимаем комментарий
        if ($this->request->method('post') && $this->request->post('comment')) {
            $comment = new \stdClass;
            $comment->name  = $this->request->post('name');
            $comment->email = $this->request->post('email');
            $comment->text  = $this->request->post('text');
            $captcha_code   =  $this->request->post('captcha_code', 'string');
            
            // Передадим комментарий обратно в шаблон - при ошибке нужно будет заполнить форму
            $this->design->assign('comment_text', $comment->text);
            $this->design->assign('comment_name', $comment->name);
            $this->design->assign('comment_email', $comment->email);

            // Проверяем капчу и заполнение формы
            if ($this->settings->captcha_product && !$validate->verifyCaptcha('captcha_product', $captcha_code)) {
                $this->design->assign('error', 'captcha', true);
            } elseif (!$validate->isName($comment->name, true)) {
                $this->design->assign('error', 'empty_name', true);
            } elseif (!$validate->isComment($comment->text, true)) {
                $this->design->assign('error', 'empty_comment', true);
            } elseif (!$validate->isEmail($comment->email)) {
                $this->design->assign('error', 'empty_email', true);
            } else {
                // Создаем комментарий
                $comment->object_id = $product->id;
                $comment->type      = 'product';
                $comment->ip        = $_SERVER['REMOTE_ADDR'];
                $comment->lang_id   = $_SESSION['lang_id'];

                // Добавляем комментарий в базу
                $commentId = $commentsEntity->add($comment);
                
                // Отправляем email
                $notify->emailCommentAdmin($commentId);

                $this->response->redirectTo($_SERVER['REQUEST_URI'].'#comment_'.$commentId);
                return;
            }
        }
        
        // Связанные товары
        $relatedIds = [];
        $relatedProducts = [];
        foreach ($productsEntity->getRelatedProducts($product->id) as $p) {
            $relatedIds[] = $p->related_id;
            $relatedProducts[$p->related_id] = null;
        }
        
        if (!empty($relatedIds)) {
            $relatedFilter = [
                'id' => $relatedIds,
                'limit' => count($relatedIds),
                'visible' => 1,
                'in_stock' => 1,
            ];
            foreach ($productsLogic->getProductList($relatedFilter) as $p) {
                $relatedProducts[$p->id] = $p;
            }
            foreach ($relatedProducts as $id=>$r) {
                if ($r === null) {
                    unset($relatedProducts[$id]);
                }
            }
            $this->design->assign('related_products', $relatedProducts);
        }

        //Связянные статьи для товара
        $relatedPosts = $blogEntity->getRelatedProducts(['product_id' => $product->id]);
        if (!empty($relatedPosts)) {
            $filterPost['visible'] = 1;
            foreach ($relatedPosts as $r_post) {
                $filterPost['id'][] = $r_post->post_id;
            }
            $posts = $blogEntity->find($filterPost);
            $this->design->assign('related_posts', $posts);
        }
        
        // Отзывы о товаре
        $comments = $commentsEntity->find([
            'has_parent' => false,
            'type' => 'product',
            'object_id' => $product->id,
            'approved' => 1,
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]);
        $children = [];
        $childrenFilter = [
            'has_parent' => true,
            'type' => 'product',
            'object_id' => $product->id,
            'approved' => 1,
        ];
        foreach ($commentsEntity->find($childrenFilter) as $c) {
            $children[$c->parent_id][] = $c;
        }

        // И передаем его в шаблон
        $this->design->assign('product', $product);
        $this->design->assign('comments', $comments);
        $this->design->assign('children', $children);
        
        // Категория и бренд товара
        $brand = $brandsEntity->get(intval($product->brand_id));
        if (!empty($brand) && $brand->visible) {
            $this->design->assign('brand', $brand);
        }
        
        $category = $categoriesEntity->get((int)$product->main_category_id);
        $this->design->assign('category', $category);

        // Соседние товары
        if (!empty($category)) {
            $neighborsProducts = $productsEntity->getNeighborsProducts($category->id, $product->position);
            $this->design->assign('next_product', $neighborsProducts['next']);
            $this->design->assign('prev_product', $neighborsProducts['prev']);
        }
        
        $productsLogic->setBrowsedProduct($product->id);

        $defaultProductsSeoPattern = (object)$this->settings->default_products_seo_pattern;
        $parts = [
            '{$brand}'    => ($this->design->get_var('brand') ? $this->design->get_var('brand')->name : ''),
            '{$product}'  => ($product->name ? $product->name : ''),
            '{$price}'    => ($product->variant->price != null ? $moneyCore->convert($product->variant->price, $this->currency->id, false).' '.$this->currency->sign : ''),
            '{$sitename}' => ($this->settings->site_name ? $this->settings->site_name : '')
        ];
        
        //Автоматичекска генерация мета тегов и описания товара
        if (!empty($category)) {
            $parts['{$category}']    = ($category->name ? $category->name : '');
            $parts['{$category_h1}'] = ($category->name_h1 ? $category->name_h1 : '');
            
            if (!empty($product->features)) {
                foreach ($product->features as $feature) {
                    if ($feature->auto_name_id) {
                        $parts['{$' . $feature->auto_name_id . '}'] = $feature->name;
                    }
                    if ($feature->auto_value_id) {
                        $parts['{$' . $feature->auto_value_id . '}'] = $feature->stingify_values;
                    }
                }
            }

            if (!empty($category->auto_meta_title)) {
                $autoMetaTitle = $category->auto_meta_title;
            } elseif (!empty($defaultProductsSeoPattern->auto_meta_title)) {
                $autoMetaTitle = $defaultProductsSeoPattern->auto_meta_title;
            } else {
                $autoMetaTitle = $product->meta_title;
            }

            if (!empty($category->auto_meta_keywords)) {
                $autoMetaKeywords = $category->auto_meta_keywords;
            } elseif (!empty($defaultProductsSeoPattern->auto_meta_keywords)) {
                $autoMetaKeywords = $defaultProductsSeoPattern->auto_meta_keywords;
            } else {
                $autoMetaKeywords = $product->meta_keywords;
            }

            if (!empty($category->auto_meta_desc)) {
                $autoMetaDescription = $category->auto_meta_desc;
            } elseif (!empty($defaultProductsSeoPattern->auto_meta_desc)) {
                $autoMetaDescription = $defaultProductsSeoPattern->auto_meta_desc;
            } else {
                $autoMetaDescription = $product->meta_description;
            }

            if (!empty($category->auto_description) && empty($product->description)) {
                $product->description = strtr($category->auto_description, $parts);
                $product->description = preg_replace('/\{\$[^\$]*\}/', '', $product->description);
            } elseif (!empty($defaultProductsSeoPattern->auto_description) && empty($product->description)) {
                $product->description = strtr($defaultProductsSeoPattern->auto_description, $parts);
                $product->description = preg_replace('/\{\$[^\$]*\}/', '', $product->description);
            }
        } else {

            if (!empty($defaultProductsSeoPattern->auto_meta_title)) {
                $autoMetaTitle = $defaultProductsSeoPattern->auto_meta_title;
            } else {
                $autoMetaTitle = $product->meta_title;
            }

            if (!empty($defaultProductsSeoPattern->auto_meta_keywords)) {
                $autoMetaKeywords = $defaultProductsSeoPattern->auto_meta_keywords;
            } else {
                $autoMetaKeywords = $product->meta_keywords;
            }

            if (!empty($defaultProductsSeoPattern->auto_meta_desc)) {
                $autoMetaDescription = $defaultProductsSeoPattern->auto_meta_desc;
            } else {
                $autoMetaDescription = $product->meta_description;
            }

            if (!empty($defaultProductsSeoPattern->auto_description) && empty($product->description)) {
                $product->description = strtr($defaultProductsSeoPattern->auto_description, $parts);
                $product->description = preg_replace('/\{\$[^\$]*\}/', '', $product->description);
            }
        }

        $autoMetaTitle = strtr($autoMetaTitle, $parts);
        $autoMetaKeywords = strtr($autoMetaKeywords, $parts);
        $autoMetaDescription = strtr($autoMetaDescription, $parts);

        $autoMetaTitle = preg_replace('/\{\$[^\$]*\}/', '', $autoMetaTitle);
        $autoMetaKeywords = preg_replace('/\{\$[^\$]*\}/', '', $autoMetaKeywords);
        $autoMetaDescription = preg_replace('/\{\$[^\$]*\}/', '', $autoMetaDescription);
        
        $this->design->assign('meta_title', $autoMetaTitle);
        $this->design->assign('meta_keywords', $autoMetaKeywords);
        $this->design->assign('meta_description', $autoMetaDescription);

        $this->response->setContent($this->design->fetch('product.tpl'));
    }
    
    public function rating(Products $productsEntity)
    {
        if (isset($_POST['id']) && is_numeric($_POST['rating'])) {
            $productId = intval(str_replace('product_', '', $_POST['id']));
            $rating = floatval($_POST['rating']);

            if (!isset($_SESSION['rating_ids'])) {
                $_SESSION['rating_ids'] = [];
            }
            if (!in_array($productId, $_SESSION['rating_ids'])) {
                $product = $productsEntity->cols([
                    'rating',
                    'votes',
                ])->get($productId);
                if(!empty($product)) {
                    $rate = ($product->rating * $product->votes + $rating) / ($product->votes + 1);
                    
                    $productsEntity->update($productId, ['rating'=>$rate, 'votes' => ($product->votes + 1)]);
                    
                    $_SESSION['rating_ids'][] = $productId;
                    $this->response->setContent(json_encode($rate), 'json');
                } else {
                    $this->response->setContent(json_encode(-1), 'json');
                }
            } else {
                $this->response->setContent(json_encode(0), 'json');
            }
        } else {
            $this->response->setContent(json_encode(-1), 'json');
        }
    }
}
