<?php


namespace Okay\Admin\Requests;


use Okay\Core\Phone;
use Okay\Core\Request;
use Okay\Core\Modules\Extender\ExtenderFacade;

class BackendOrdersRequest
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function postOrder()
    {
        $order = new \stdClass();
        $order->id = $this->request->post('id', 'integer');
        $order->name = $this->request->post('name');
        $order->email = $this->request->post('email');
        $order->phone = Phone::toSave($this->request->post('phone'));
        $order->address = $this->request->post('address');
        $order->comment = $this->request->post('comment');
        $order->note = $this->request->post('note');
        $order->discount = $this->request->post('discount', 'float');
        $order->coupon_discount = $this->request->post('coupon_discount', 'float');
        $order->delivery_id = $this->request->post('delivery_id', 'integer');
        $order->delivery_price = $this->request->post('delivery_price', 'float');
        $order->payment_method_id = $this->request->post('payment_method_id', 'integer');
        $order->paid = $this->request->post('paid', 'integer');
        $order->user_id = $this->request->post('user_id', 'integer');
        $order->lang_id = $this->request->post('entity_lang_id', 'integer');

        return ExtenderFacade::execute(__METHOD__, $order, func_get_args());
    }

    public function postPurchases()
    {
        $purchases = [];
        if ($this->request->post('purchases')) {
            foreach ($this->request->post('purchases') as $n => $va) foreach ($va as $i => $v) {
                if (empty($purchases[$i])) {
                    $purchases[$i] = new \stdClass;
                }
                $purchases[$i]->$n = $v;
            }
        }

        return ExtenderFacade::execute(__METHOD__, $purchases, func_get_args());
    }

    public function getPage()
    {
        $page = $this->request->get('page');
        return ExtenderFacade::execute(__METHOD__, $page, func_get_args());
    }
}