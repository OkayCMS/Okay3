<?php


namespace Okay\Admin\Requests;


use Okay\Core\Request;
use Okay\Core\Modules\Extender\ExtenderFacade;

class BackendOrderSettingsRequest
{
    /**
     * @var Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function postNewStatuses()
    {
        $newStatusNames = $this->request->post('new_name');
        $newParams      = $this->request->post('new_is_close');
        $newColors      = $this->request->post('new_color');

        $newStatuses = [];
        foreach ($newStatusNames as $id => $name) {
            if (!empty($name)) {
                $status = new \stdClass();
                $status->name     = $name;
                $status->is_close = $newParams[$id];
                $status->color    = $newColors[$id];
                $newStatuses[$id] = $status;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $newStatuses, func_get_args());
    }

    public function postStatuses()
    {
        $statusNames = $this->request->post('name');
        $params      = $this->request->post('is_close');
        $colors      = $this->request->post('color');

        $statuses = [];
        foreach ($statusNames as $id=>$name) {
            if (!empty($name)) {
                $status = new \stdClass();
                $status->name     = $name;
                $status->is_close = $params[$id];
                $status->color    = $colors[$id];
                $statuses[$id] = $status;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $statuses, func_get_args());
    }

    public function postStatus()
    {
        $status = $this->request->post('status');
        return ExtenderFacade::execute(__METHOD__, $status, func_get_args());
    }

    public function postPositions()
    {
        $positions = $this->request->post('positions');
        return ExtenderFacade::execute(__METHOD__, $positions, func_get_args());
    }

    public function postCheck()
    {
        $check = $this->request->post('check');
        return ExtenderFacade::execute(__METHOD__, $check, func_get_args());
    }

    public function postNewLabels()
    {
        $newLabelNames = $this->request->post('new_name');
        $newColors     = $this->request->post('new_color');

        $newLabels = [];
        foreach ($newLabelNames as $id=>$name) {
            if (!empty($name)) {
                $label = new \stdClass();
                $label->name  = $name;
                $label->color = $newColors[$id];
                $newLabels[] = $label;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $newLabels, func_get_args());
    }

    public function postLabels()
    {
        $labelNames = $this->request->post('name');
        $colors     = $this->request->post('color');

        $labels = [];
        foreach ($labelNames as $id=>$name) {
            if (!empty($name)) {
                $label = new \stdClass();
                $label->name  = $name;
                $label->color = $colors[$id];
                $labels[$id]  = $labels;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $labels, func_get_args());
    }
}