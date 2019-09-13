<?php


namespace Okay\Modules\OkayCMS\LiqPay;


use Okay\Core\EntityFactory;
use Okay\Core\Modules\AbstractModule;
use Okay\Core\Modules\Payments\PaymentFormInterface;
use Okay\Core\Money;
use Okay\Core\Router;
use Okay\Entities\CurrenciesEntity;
use Okay\Entities\OrdersEntity;
use Okay\Entities\PaymentsEntity;

class PaymentForm extends AbstractModule implements PaymentFormInterface
{	
    
    private $entityFactory;
    private $money;
    
    public function __construct(EntityFactory $entityFactory, Money $money)
    {
        parent::__construct();
        $this->entityFactory = $entityFactory;
        $this->money = $money;
    }

    /**
     * @inheritDoc
     */
    public function checkoutForm($orderId)
	{
		/** @var OrdersEntity $ordersEntity */
	    $ordersEntity = $this->entityFactory->get(OrdersEntity::class);
	    
		/** @var PaymentsEntity $paymentsEntity */
	    $paymentsEntity = $this->entityFactory->get(PaymentsEntity::class);
	    
		/** @var CurrenciesEntity $currenciesEntity */
	    $currenciesEntity = $this->entityFactory->get(CurrenciesEntity::class);
	    
		$order = $ordersEntity->get((int)$orderId);
		$liqPayOrderId = $order->id . "-" . rand(100000, 999999);
		$paymentMethod = $paymentsEntity->get($order->payment_method_id);
		$paymentCurrency = $currenciesEntity->get(intval($paymentMethod->currency_id));
		$settings = $paymentsEntity->getPaymentSettings($paymentMethod->id);
		
		$price = round($this->money->convert($order->total_price, $paymentMethod->currency_id, false), 2);	

		// описание заказа
		// order description
		$desc = 'Оплата заказа №'.$order->id;

		$resultUrl = Router::generateUrl('order', ['url' => $order->url], true);
        $serverUrl = Router::generateUrl('OkayCMS_LiqPay_callback', [], true);
		
		$privateKey = $settings['liq_pay_private_key'];
		$publicKey = $settings['liq_pay_public_key'];
		$sign = base64_encode(sha1($privateKey.$price.$paymentCurrency->code.$publicKey.$liqPayOrderId.'buy'.$desc.$resultUrl.$serverUrl, 1));

        $this->design->assign('public_key', $publicKey);
        $this->design->assign('price', $price);
        $this->design->assign('payment_currency', $paymentCurrency);
        $this->design->assign('desc', $desc);
        $this->design->assign('liq_pay_order_id', $liqPayOrderId);
        $this->design->assign('result_url', $resultUrl);
        $this->design->assign('server_url', $serverUrl);
        $this->design->assign('sign', $sign);

        return $this->design->fetch('form.tpl');
	}
}