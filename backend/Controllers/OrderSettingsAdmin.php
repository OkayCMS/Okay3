<?php


namespace Okay\Admin\Controllers;


use Okay\Admin\Helpers\BackendOrderSettingsHelper;
use Okay\Admin\Helpers\BackendOrdersHelper;
use Okay\Admin\Requests\BackendOrderSettingsRequest;

class OrderSettingsAdmin extends IndexAdmin
{

    public function fetch(
        BackendOrdersHelper         $backendOrdersHelper,
        BackendOrderSettingsRequest $orderSettingsRequest,
        BackendOrderSettingsHelper  $backendOrderSettingsHelper
    ){
        /*Статусы заказов*/
        if ($orderSettingsRequest->postStatus()) {
            $positions = $orderSettingsRequest->postPositions();
            $backendOrderSettingsHelper->sortStatusPositions($positions);

            $newStatuses = $orderSettingsRequest->postNewStatuses();
            $backendOrderSettingsHelper->addNewStatuses($newStatuses);

            $statuses = $orderSettingsRequest->postStatuses();
            $backendOrderSettingsHelper->updateStatuses($statuses);

            $idsToDelete = $orderSettingsRequest->postCheck();
            if (!empty($idsToDelete) && $backendOrderSettingsHelper->statusCanBeDeleted()) {
                $result = $backendOrderSettingsHelper->deleteStatuses($idsToDelete);
                $this->design->assign("error_status", $result);
            }
        }
        // Отображение
        $ordersStatuses = $backendOrdersHelper->findStatuses();
        $this->design->assign('orders_statuses', $ordersStatuses);

        /*Метки заказов*/
        if ($this->request->post('labels')) {
            $positions = $orderSettingsRequest->postPositions();
            $backendOrderSettingsHelper->sortLabelPositions($positions);

            $newLabels = $orderSettingsRequest->postNewLabels();
            $backendOrderSettingsHelper->addNewLabels($newLabels);

            $labels = $orderSettingsRequest->postLabels();
            $backendOrderSettingsHelper->updateLabels($labels);

            $idsToDelete = $orderSettingsRequest->postCheck();
            if (!empty($idsToDelete)) {
                $backendOrderSettingsHelper->deleteLabels($idsToDelete);
            }
        }
        // Отображение
        $labels = $backendOrdersHelper->findLabels();
        $this->design->assign('labels', $labels);

        $this->response->setContent($this->design->fetch('order_settings.tpl'));
    }

}

