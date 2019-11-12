<?php


namespace Okay\Modules\OkayCMS\FastOrder\Controllers;


use Okay\Core\Notify;
use Okay\Core\Router;
use Okay\Core\Languages;
use Okay\Core\EntityFactory;
use Okay\Helpers\OrdersHelper;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PurchasesEntity;
use Okay\Controllers\AbstractController;
use Okay\Modules\OkayCMS\FastOrder\FormSpecification;

class FastOrderController extends AbstractController
{
    public function createOrder(
        FormSpecification $formSpecification,
        EntityFactory     $entityFactory,
        OrdersHelper      $ordersHelper,
        Languages         $languages,
        Notify            $notify
    ){
        if (!$this->request->method('post')) {
            return $this->response->setContent(json_encode(['errors' => ['Request must be post']]), RESPONSE_JSON);
        }

        $errors = $formSpecification->validateFields(
            [
                'name'  => ['required'],
                'phone' => ['required', 'phone'],
            ],
            [
                'name'  => $this->request->post('name'),
                'phone' => $this->request->post('phone'),
            ]
        );

        if (!empty($errors)) {
            return $this->response->setContent(json_encode(['errors' => $errors]), RESPONSE_JSON);
        }

        $order = new \stdClass();
        $order->name    = $this->request->post('name');
        $order->phone   = $this->request->post('phone');
        $order->email   = '';
        $order->address = '';
        $order->comment = 'Быстрый заказ';
        $order->lang_id = $languages->getLangId();
        $order->ip      = $_SERVER['REMOTE_ADDR'];

        /** @var OrdersEntity $ordersEntity */
        $ordersEntity = $entityFactory->get(OrdersEntity::class);
        $orderId      = $ordersEntity->add($order);

        /** @var PurchasesEntity $purchasesEntity */
        $purchasesEntity = $entityFactory->get(PurchasesEntity::class);

        $amount = $this->request->post('amount', 'integer');
        if ($amount <= 0) {
            $amount = 1;
        }

        $purchasesEntity->add([
            'order_id'   => $orderId,
            'variant_id' => $this->request->post('variant_id'),
            'amount'     => $amount
        ]);

        $ordersHelper->finalCreateOrderProcedure($order);
        $ordersEntity->updateTotalPrice($orderId);

        $order = $ordersEntity->get((int) $orderId);
        $notify->emailOrderUser($order->id);
        $notify->emailOrderAdmin($order->id);

        return $this->response->setContent(json_encode([
            'success'           => 1,
            'redirect_location' => Router::generateUrl('order', ['url' => $order->url], true)
        ]), RESPONSE_JSON);
    }
}