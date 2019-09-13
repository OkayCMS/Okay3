<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\CategoriesEntity;
use Okay\Entities\FeaturesEntity;
use Okay\Entities\FeaturesAliasesEntity;
use Okay\Entities\SEOFilterPatternsEntity;

class SeoFilterPatternsAdmin extends IndexAdmin
{

    public function fetch(
        SEOFilterPatternsEntity $SEOFilterPatternsEntity,
        FeaturesEntity $featuresEntity,
        FeaturesAliasesEntity $featuresAliasesEntity,
        CategoriesEntity $categoriesEntity
    ) {
        $this->design->setTemplatesDir('backend/design/html');
        $this->design->setCompiledDir('backend/design/compiled');

        if ($this->request->post("ajax")){

            $result = new \stdClass();
            if ($this->request->post("action") == "get_features") {
                $categoryId = $this->request->post("category_id", "integer");
                $result->features = $featuresEntity->find([
                    'category_id'=>$categoryId,
                    'in_filter'=>1
                ]);
                $result->success = true;
            }
            /*Получение SEO шаблонов*/
            if ($this->request->post("action") == "get") {

                $category = $categoriesEntity->get($this->request->post("category_id", "integer"));
                if (!empty($category->id)) {
                    $featuresIds = [];
                    $patterns = [];
                    $features = [];
                    foreach ($SEOFilterPatternsEntity->find(['category_id'=>$category->id]) as $p) {
                        $patterns[$p->id] = $p;
                        if ($p->feature_id) {
                            $featuresIds[] = $p->feature_id;
                        }
                    }

                    $featuresIds = array_unique($featuresIds);
                    foreach ($featuresEntity->find(['id'=>$featuresIds]) as $f) {
                        $features[$f->id] = $f;
                    }

                    foreach ($patterns as $p) {
                        if ($p->feature_id && isset($features[$p->feature_id])) {
                            $p->feature = $features[$p->feature_id];
                        }
                    }
                    $this->design->assign('patterns', $patterns);
                    $this->design->assign("category", $category);
                    $featuresAliases = $featuresAliasesEntity->find();
                    $this->design->assign("features_aliases", $featuresAliases);
                    $result->success = true;
                } else {
                    $result->success = false;
                }
                $result->tpl = $this->design->fetch("seo_filter_patterns_ajax.tpl");
            }

            /*Обновление шаблона данных категории*/
            if ($this->request->post("action") == "set") {

                $this->settings->max_filter_brands          = $this->request->post('max_filter_brands', 'integer', 1);
                $this->settings->max_filter_filter          = $this->request->post('max_filter_filter', 'integer', 1);
                $this->settings->max_filter_features_values = $this->request->post('max_filter_features_values', 'integer', 1);
                $this->settings->max_filter_features        = $this->request->post('max_filter_features', 'integer', 1);
                $this->settings->max_filter_depth           = $this->request->post('max_filter_depth', 'integer', 1);

                $result->success = true;
                
                $category = new \stdClass();
                $category->id = $this->request->post("category_id", "integer");
                if ($category = $categoriesEntity->get($category->id)) {
                    $seoFilterPatterns = $this->request->post('seo_filter_patterns');
                    $patterns = [];
                    $patternsIds = [];
                    if (is_array($seoFilterPatterns)) {

                        foreach ($this->request->post('seo_filter_patterns') as $n=>$pa) {
                            foreach ($pa as $i=>$p) {
                                if (empty($patterns[$i])) {
                                    $patterns[$i] = new \stdClass;
                                }
                                $patterns[$i]->$n = $p;
                                if ($n == 'id') {
                                    $patternsIds[] = $p;
                                }
                            }
                        }
                    }
                    
                    // Удалим паттерны которые не запостили
                    $currentPatterns = $SEOFilterPatternsEntity->find(['category_id' => $category->id]);
                    foreach ($currentPatterns as $current_pattern) {
                        if (!in_array($current_pattern->id, $patternsIds)) {
                            $SEOFilterPatternsEntity->delete($current_pattern->id);
                        }
                    }

                    if ($patterns) {
                        foreach ($patterns as $pattern) {
                            if (!$pattern->feature_id) {
                                $pattern->feature_id = null;
                            }
                            if (!empty($pattern->id)) {
                                $SEOFilterPatternsEntity->update($pattern->id, $pattern);
                            } else {
                                $pattern->category_id = $category->id;
                                $pattern->id = $SEOFilterPatternsEntity->add($pattern);
                            }
                        }
                    }

                    $featuresIds = [];
                    $patterns = [];
                    $features = [];
                    foreach ($SEOFilterPatternsEntity->find(['category_id'=>$category->id]) as $p) {
                        $patterns[$p->id] = $p;
                        if ($p->feature_id) {
                            $featuresIds[] = $p->feature_id;
                        }
                    }

                    $featuresIds = array_unique($featuresIds);
                    foreach ($featuresEntity->find(['id'=>$featuresIds]) as $f) {
                        $features[$f->id] = $f;
                    }

                    foreach ($patterns as $p) {
                        if ($p->feature_id && isset($features[$p->feature_id])) {
                            $p->feature = $features[$p->feature_id];
                        }
                    }
                    $this->design->assign('patterns', $patterns);
                    $this->design->assign("category", $category);
                    $featuresAliases = $featuresAliasesEntity->find();
                    $this->design->assign("features_aliases", $featuresAliases);
                    $result->tpl = $this->design->fetch("seo_filter_patterns_ajax.tpl");
                }
            }

            if ($result) {
                $this->response->setContent(json_encode($result), RESPONSE_JSON);
                return;
            }
        }

        $categories = $categoriesEntity->getCategoriesTree();
        $this->design->assign('categories', $categories);

        $this->response->setContent($this->design->fetch('seo_filter_patterns.tpl'));
    }
}
