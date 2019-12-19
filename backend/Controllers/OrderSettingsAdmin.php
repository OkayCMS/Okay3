<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendOrderSettingsHelper;
use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Admin\Requests\BackendOrderSettingsRequest;
use Okay\Core\BackendPostRedirectGet;

class OrderSettingsAdmin extends IndexAdmin
{

    public function fetch(
        BackendOrdersHelper         $backendOrdersHelper,
        BackendOrderSettingsRequest $orderSettingsRequest,
        BackendOrderSettingsHelper  $backendOrderSettingsHelper,
        BackendPostRedirectGet      $backendPostRedirectGet
    ){
        /*Статусы заказов*/
        if ($this->request->post('statuses')) {
            if ($positions = $orderSettingsRequest->postPositions()) {
                $backendOrderSettingsHelper->sortStatusPositions($positions);
            }

            $statuses = $orderSettingsRequest->postStatuses();
            $backendOrderSettingsHelper->updateStatuses($statuses);

            $idsToDelete = $orderSettingsRequest->postCheck();
            if (!empty($idsToDelete) && $backendOrderSettingsHelper->statusCanBeDeleted()) {
                $backendOrderSettingsHelper->deleteStatuses($idsToDelete);
            }
            $backendPostRedirectGet->redirect();
        }
        

        /*Метки заказов*/
        if ($this->request->post('labels')) {
            if ($positions = $orderSettingsRequest->postPositions()) {
                $backendOrderSettingsHelper->sortLabelPositions($positions);
            }

            $labels = $orderSettingsRequest->postLabels();
            $backendOrderSettingsHelper->updateLabels($labels);

            $idsToDelete = $orderSettingsRequest->postCheck();
            if (!empty($idsToDelete)) {
                $backendOrderSettingsHelper->deleteLabels($idsToDelete);
            }
            $backendPostRedirectGet->redirect();
        }
        // Отображение
        $ordersStatuses = $backendOrdersHelper->findStatuses();
        $this->design->assign('orders_statuses', $ordersStatuses);
        
        $labels = $backendOrdersHelper->findLabels();
        $this->design->assign('labels', $labels);

        $this->response->setContent($this->design->fetch('order_settings.tpl'));
    }

}

