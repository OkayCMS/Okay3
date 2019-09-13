<?php


namespace Okay\Core\Modules\Payments;


interface PaymentFormInterface
{

    /**
     * @param int $orderId
     * @return string HTML payment form
     */
    public function checkoutForm($orderId);
}