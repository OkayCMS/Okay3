<?php


namespace Okay\Admin\Controllers;


use Okay\Entities\MenuEntity;
use Okay\Entities\MenuItemsEntity;

class MenuAdmin extends IndexAdmin
{

    public function fetch(MenuEntity $menuEntity, MenuItemsEntity $menuItemsEntity)
    {

        $menuItems = [];
        
        /*Принимаем данные о меню*/
        if ($this->request->method('POST')) {
            $menu = new \stdClass();
            $menu->id       = $this->request->post('id', 'integer');
            $menu->group_id = trim($this->request->post('group_id', 'string'));
            $menu->name     = $this->request->post('name');
            $menu->visible  = $this->request->post('visible', 'integer');
            $menu->group_id = preg_replace("/[\s]+/ui", '', $menu->group_id);
            $menu->group_id = strtolower(preg_replace("/[^0-9a-z_]+/ui", '', $menu->group_id));

            if ($this->request->post('menu_items')) {
                foreach ($this->request->post('menu_items') as $field => $values) {
                    foreach ($values as $i => $v) {
                        if (empty($menuItems[$i])) {
                            $menuItems[$i] = new \stdClass();
                            $menuItems[$i]->i_tm = $i;
                        }
                        $menuItems[$i]->$field = $v;
                    }
                }
                // сортируем по родителю
                usort($menuItems, function ($item1, $item2) {
                    if ($item1->parent_index == $item2->parent_index) {
                        return $item1->i_tm - $item2->i_tm;
                    }
                    return strcmp($item1->parent_index, $item2->parent_index);
                });
                $tm = [];
                
                $local = [trim($this->request->getRootUrl(), "/"), trim(preg_replace("~^https?://~", "", $this->request->getRootUrl()), "/")];
                foreach ($menuItems as $key => $item) {
                    foreach ($local as $l) {
                        $item->url = preg_replace("~^$l/?~", "", $item->url);
                    }
                    $tm[$item->index] = $item;
                }
                $menuItems = $tm;
            }

            if (($m = $menuEntity->get((string)$menu->group_id)) && $m->id!=$menu->id) {
                $this->design->assign('message_error', 'group_id_exists');
                $menuItems = $this->buildTree($menuItems);
            } elseif (empty($menu->group_id)) {
                $this->design->assign('message_error', 'empty_group_id');
                $menuItems = $this->buildTree($menuItems);
            } else {
                /*Добавляем/обновляем меню*/
                if (empty($menu->id)) {
                    $menu->id = $menuEntity->add($menu);
                    $this->design->assign('message_success', 'added');
                } else {
                    $menuEntity->update($menu->id, $menu);
                    $this->design->assign('message_success', 'updated');
                }
                if ($menu->id) {
                    $menuItemsIds = [];
                    if (is_array($menuItems)) {
                        foreach ($menuItems as $i=>$item) {
                            if ($item->parent_index > 0) {
                                if (!isset($menuItems[$item->parent_index]->id)) {
                                    unset($menuItems[$i]);
                                    continue;
                                }
                                $item->parent_id = $menuItems[$item->parent_index]->id;
                            } else {
                                $item->parent_id = 0;
                            }

                            $item->menu_id = $menu->id;
                            unset($item->index);
                            unset($item->parent_index);
                            unset($item->i_tm);
                            if (empty($item->id)) {
                                $item->id = $menuItemsEntity->add($item);
                            } else {
                                $menuItemsEntity->update($item->id, $item);
                            }
                            if ($item->id) {
                                $menuItemsIds[] = $item->id;
                            }
                        }
                    }

                    // удаляем не переданные элементы меню
                    $currentMenuItemsIds = $menuItemsEntity->cols(['id'])->find(['menu_id' => $menu->id]);
                    foreach ($currentMenuItemsIds as $menuItemId) {
                        if (!in_array($menuItemId, $menuItemsIds)) {
                            $menuItemsEntity->delete($menuItemId);
                        }
                    }

                    // Отсортировать  элементы меню
                    asort($menuItemsIds);
                    $i = 0;
                    foreach($menuItemsIds as $menu_item_id) {
                        $menuItemsEntity->update($menuItemsIds[$i], ['position'=>$menu_item_id]);
                        $i++;
                    }

                    $menuItems = $menuItemsEntity->getMenuItemsTree((int)$menu->id);
                }
                $menu = $menuEntity->get($menu->id);
            }
        } else {
            /*Отображение меню*/
            $id = $this->request->get('id', 'integer');
            if (!empty($id)) {
                $menu = $menuEntity->get((int)$id);
                if (!empty($menu->id)) {
                    $menuItems = $menuItemsEntity->getMenuItemsTree((int)$menu->id);
                }
            } else {
                $menu = new \stdClass();
                $menu->visible = 1;
            }
        }

        $this->design->assign('menu', $menu);
        $this->design->assign('menu_items', $menuItems);
        $this->response->setContent($this->design->fetch('menu.tpl'));
    }

    private function buildTree($items) {
        $tree = new \stdClass();
        $tree->submenus = array();

        // Указатели на узлы дерева
        $pointers = array();
        $pointers[0] = &$tree;

        $finish = false;
        // Не кончаем, пока не кончатся элементы, или пока ниодну из оставшихся некуда приткнуть
        while (!empty($items) && !$finish) {
            $flag = false;
            // Проходим все выбранные элементы
            foreach ($items as $k => $item) {
                if (isset($pointers[$item->parent_index])) {
                    // В дерево элементов меню (через указатель) добавляем текущий элемент
                    $pointers[$item->index] = $pointers[$item->parent_index]->submenus[] = $item;

                    // Убираем использованный элемент из массива
                    unset($items[$k]);
                    $flag = true;
                }
            }
            if (!$flag) $finish = true;
        }
        unset($pointers[0]);
        return $tree->submenus;
    }

}
