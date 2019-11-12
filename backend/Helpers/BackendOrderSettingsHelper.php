<?php


namespace Okay\Admin\Helpers;


use Okay\Core\EntityFactory;
use Okay\Entities\OrderLabelsEntity;
use Okay\Entities\OrderStatusEntity;
use Okay\Core\Modules\Extender\ExtenderFacade;

class BackendOrderSettingsHelper
{
    /**
     * @var OrderStatusEntity
     */
    private $orderStatusEntity;

    /**
     * @var OrderLabelsEntity
     */
    private $orderLabelsEntity;

    public function __construct(EntityFactory $entityFactory)
    {
        $this->orderStatusEntity = $entityFactory->get(OrderStatusEntity::class);
        $this->orderLabelsEntity = $entityFactory->get(OrderLabelsEntity::class);
    }

    public function addNewStatuses($newStatuses)
    {
        foreach ($newStatuses as $status) {
            $this->orderStatusEntity->add($status);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function updateStatuses($statuses)
    {
        foreach ($statuses as $id => $status) {
            $this->orderStatusEntity->update($id, $status);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function sortStatusPositions($positions)
    {
        $ids = array_keys($positions);
        sort($positions);
        foreach ($positions as $i=>$position) {
            $this->orderStatusEntity->update($ids[$i], ['position'=>$position]);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function sortLabelPositions($positions)
    {
        $ids = array_keys($positions);
        sort($positions);
        foreach ($positions as $i=>$position) {
            $this->orderLabelsEntity->update($ids[$i], ['position'=>$position]);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function deleteStatuses($ids)
    {
        $result = $this->orderStatusEntity->delete($ids);
        return ExtenderFacade::execute(__METHOD__, $result, func_get_args());
    }

    public function statusCanBeDeleted()
    {
        $result = $this->orderStatusEntity->count() > 1;
        return ExtenderFacade::execute(__METHOD__, $result, func_get_args());
    }

    public function addNewLabels($newLabels)
    {
        foreach($newLabels as $label) {
            $this->orderLabelsEntity->add($label);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function updateLabels($labels)
    {
        foreach($labels as $id => $label) {
            $this->orderLabelsEntity->update($id, $label);
        }

        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }

    public function deleteLabels($ids)
    {
        $this->orderLabelsEntity->delete($ids);
        ExtenderFacade::execute(__METHOD__, null, func_get_args());
    }
}