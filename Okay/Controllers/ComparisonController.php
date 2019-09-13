<?php


namespace Okay\Controllers;


use Okay\Core\Comparison;

class ComparisonController extends AbstractController
{
    
    public function render()
    {
        $this->response->setContent($this->design->fetch('comparison.tpl'));
    }
    
    public function ajaxUpdate(Comparison $comparison)
    {

        $productId = $this->request->get('product', 'integer');
        $action = $this->request->get('action');
        if ($action == 'add') {
            $comparison->addItem($productId);
        } elseif($action == 'delete') {
            $comparison->deleteItem($productId);
        }

        $this->design->assign('comparison', $comparison->get());

        $result = $this->design->fetch('comparison_informer.tpl');        
        $this->response->setContent(json_encode($result), RESPONSE_JSON);
    }
    
}
