<?php

namespace Tests\YandexCheckout\Client;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Client;
use YandexCheckout\Common\Exceptions\ApiException;
use YandexCheckout\Common\Exceptions\JsonException;
use YandexCheckout\Common\Exceptions\ResponseProcessingException;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Helpers\StringObject;
use YandexCheckout\Request\PaymentOptionsRequest;
use YandexCheckout\Request\PaymentOptionsResponse;
use YandexCheckout\Request\PaymentOptionsResponseItem;
use YandexCheckout\Request\Payments\CreatePaymentResponse;
use YandexCheckout\Request\Payments\CreatePaymentRequest;
use YandexCheckout\Request\Payments\Payment\CancelResponse;
use YandexCheckout\Request\Payments\Payment\CreateCaptureRequest;
use YandexCheckout\Request\Payments\Payment\CreateCaptureResponse;
use YandexCheckout\Request\Payments\PaymentResponse;
use YandexCheckout\Request\Payments\PaymentsRequest;
use YandexCheckout\Request\Payments\PaymentsResponse;
use YandexCheckout\Request\Refunds\CreateRefundRequest;
use YandexCheckout\Request\Refunds\CreateRefundResponse;
use YandexCheckout\Request\Refunds\RefundResponse;
use YandexCheckout\Request\Refunds\RefundsRequest;
use YandexCheckout\Request\Refunds\RefundsResponse;

class ClientTest extends TestCase
{
    /**
     * @dataProvider paymentOptionsDataProvider
     * @param $paymentOptionsRequest
     */
    public function testPaymentOptions($paymentOptionsRequest)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('paymentOptionsFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentOptions($paymentOptionsRequest);

        self::assertSame($curlClientStub, $apiClient->getApiClient());
        $this->assertTrue($response instanceof PaymentOptionsResponse);
        foreach ($response->getItems() as $item) {
            $this->assertTrue($item instanceof PaymentOptionsResponseItem);
        }

        $items = $response->getItems();
        $item = $items[0];

        $this->assertTrue($item->getExtraFee());
        $this->assertEquals("yandex_money", $item->getPaymentMethodType());
        $this->assertEquals(array("redirect"), $item->getConfirmationTypes());
        $this->assertEquals("10.00", $item->getCharge()->getValue());
        $this->assertEquals("RUB", $item->getCharge()->getCurrency());
        $this->assertEquals("10.00", $item->getFee()->getValue());
        $this->assertEquals("RUB", $item->getFee()->getCurrency());
    }

    public function paymentOptionsDataProvider()
    {
        return array(
            array(null),
            array(PaymentOptionsRequest::builder()->setAccountId('123')->build()),
            array(
                array(
                    'account_id' => '123',
                )
            )
        );
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidPaymentOptions($httpCode, $errorResponse, $requiredException)
    {
        $paymentOptionsRequest = PaymentOptionsRequest::builder()->setAccountId('123')->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getPaymentOptions($paymentOptionsRequest);
        } catch (\Exception $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testCreatePayment()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YandexCheckout\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('createPaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment);

        self::assertSame($curlClientStub, $apiClient->getApiClient());
        self::assertTrue($response instanceof CreatePaymentResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('createPaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment(array(
                'amount' => array(
                    'value' => 123,
                    'currency' => 'USD',
                ),
                'payment_token' => \YandexCheckout\Helpers\Random::str(36),
            ), 123);

        self::assertSame($curlClientStub, $apiClient->getApiClient());
        self::assertTrue($response instanceof CreatePaymentResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"type":"error","code":"request_accepted","retry_after":1800}',
                array('http_code' => 202)
            ));

        try {
            $response = $apiClient
                ->setApiClient($curlClientStub)
                ->setAuth('shopId', 'shopPassword')
                ->createPayment($payment, 123);
            self::fail('Исключение не было выброшено');
        } catch (ApiException $e) {
            self::assertInstanceOf('YandexCheckout\Common\Exceptions\ResponseProcessingException', $e);
            return;
        }

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"type":"error","code":"request_accepted"}',
                array('http_code' => 202)
            ));

        try {
            $apiClient->setRetryTimeout(0);
            $response = $apiClient
                ->setApiClient($curlClientStub)
                ->setAuth('shopId', 'shopPassword')
                ->createPayment($payment, 123);
            self::fail('Исключение не было выброшено');
        } catch (ResponseProcessingException $e) {
            self::assertEquals(Client::DEFAULT_DELAY, $e->retryAfter);
            return;
        }
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidCreatePayment($httpCode, $errorResponse, $requiredException)
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YandexCheckout\Helpers\Random::str(36))
            ->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->createPayment($payment,123);
        } catch (ApiException $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider paymentsListDataProvider
     * @param mixed $request
     */
    public function testPaymentsList($request)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('getPaymentsFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPayments($request);

        $this->assertTrue($response instanceof PaymentsResponse);
    }

    public function paymentsListDataProvider()
    {
        return array(
            array(null),
            array(PaymentsRequest::builder()->setAccountId(12)->build()),
            array(array(
                'account_id' => 12,
            ))
        );
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidPaymentsList($httpCode, $errorResponse, $requiredException)
    {
        $payments = PaymentsRequest::builder()->setAccountId(12)->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getPayments($payments);
        } catch (ApiException $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider paymentInfoDataProvider
     * @param mixed $paymentId
     * @param string $exceptionClassName
     */
    public function testGetPaymentInfo($paymentId, $exceptionClassName = null)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($exceptionClassName !== null ? self::never() : self::once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('paymentInfoFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new Client();

        if ($exceptionClassName !== null) {
            $this->setExpectedException($exceptionClassName);
        }

        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentInfo($paymentId);

        self::assertTrue($response instanceof PaymentResponse);
    }

    public function paymentInfoDataProvider()
    {
        return array(
            array(null, '\InvalidArgumentException'),
            array(Random::str(36)),
            array(new StringObject(Random::str(36))),
            array(true, '\InvalidArgumentException'),
            array(false, '\InvalidArgumentException'),
            array(0, '\InvalidArgumentException'),
            array(1, '\InvalidArgumentException'),
            array(0.1, '\InvalidArgumentException'),
            array(Random::str(35), '\InvalidArgumentException'),
            array(Random::str(37), '\InvalidArgumentException'),
            array(new \DateTime(), '\InvalidArgumentException'),
            array(array(), '\InvalidArgumentException'),
        );
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidGetPaymentInfo($httpCode, $errorResponse, $requiredException)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getPaymentInfo(\YandexCheckout\Helpers\Random::str(36));
        } catch (ApiException $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testCapturePayment()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('capturePaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $capturePaymentRequest = array(
            'amount' => array(
                'value' => 123,
                'currency' => 'EUR',
            )
        );

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->capturePayment($capturePaymentRequest, '1ddd77af-0bd7-500d-895b-c475c55fdefc', 123);

        $this->assertTrue($response instanceof CreateCaptureResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('capturePaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $capturePaymentRequest = CreateCaptureRequest::builder()->setAmount(10)->build();

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->capturePayment($capturePaymentRequest, '1ddd77af-0bd7-500d-895b-c475c55fdefc');

        $this->assertTrue($response instanceof CreateCaptureResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"type":"error","code":"request_accepted","retry_after":123}',
                array('http_code' => 202)
            ));

        try {
            $response = $apiClient
                ->setApiClient($curlClientStub)
                ->setAuth('shopId', 'shopPassword')
                ->capturePayment($capturePaymentRequest, '1ddd77af-0bd7-500d-895b-c475c55fdefc', 123);
            self::fail('Exception not thrown');
        } catch (ApiException $e) {
            self::assertInstanceOf('YandexCheckout\Common\Exceptions\ResponseProcessingException', $e);
        }

        try {
            $apiClient->capturePayment($capturePaymentRequest, null, 123);
        } catch (\InvalidArgumentException $e) {
            // it's ok
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidCapturePayment($httpCode, $errorResponse, $requiredException)
    {
        $capturePaymentRequest = CreateCaptureRequest::builder()->setAmount(10)->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->capturePayment($capturePaymentRequest, '1ddd77af-0bd7-500d-895b-c475c55fdefc', 123);
        } catch (ApiException $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider paymentInfoDataProvider
     * @param mixed $paymentId
     * @param string|null $exceptionClassName
     */
    public function testPaymentIdCapturePayment($paymentId, $exceptionClassName = null)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($exceptionClassName === null ? self::once() : self::never())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('capturePaymentFixtures.json'),
                array('http_code' => 200)
            ));

        $capturePaymentRequest = array(
            'amount' => array(
                'value' => 123,
                'currency' => 'EUR',
            )
        );

        if ($exceptionClassName !== null) {
            $this->setExpectedException($exceptionClassName);
        }

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->capturePayment($capturePaymentRequest, $paymentId, 123);

        self::assertTrue($response instanceof CreateCaptureResponse);
    }

    /**
     * @dataProvider paymentInfoDataProvider
     * @param mixed $paymentId
     * @param string $exceptionClassName
     */
    public function testCancelPayment($paymentId, $exceptionClassName = null)
    {
        $invalid = $exceptionClassName !== null;
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($invalid ? self::never() : self::once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('cancelPaymentFixtures.json'),
                array('http_code' => 200)
            ));

        if ($invalid) {
            $this->setExpectedException($exceptionClassName);
        }

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->cancelPayment($paymentId, 123);

        $this->assertTrue($response instanceof CancelResponse);
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidCancelPayment($httpCode, $errorResponse, $requiredException)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->cancelPayment(\YandexCheckout\Helpers\Random::str(36));
        } catch (ApiException $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider refundsDataProvider
     * @param mixed $refundsRequest
     */
    public function testGetRefunds($refundsRequest)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects(self::once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('refundsInfoFixtures.json'),
                array('http_code' => 200)
            ));

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getRefunds($refundsRequest);

        $this->assertTrue($response instanceof RefundsResponse);
    }

    public function refundsDataProvider()
    {
        return array(
            array(null),
            array(RefundsRequest::builder()->setAccountId(123)->build()),
            array(array(
                'account_id' => 123,
            )),
        );
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidGetRefunds($httpCode, $errorResponse, $requiredException)
    {
        $refundsRequest = RefundsRequest::builder()->setAccountId(123)->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getRefunds($refundsRequest);
        } catch (ApiException $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testCreateRefund()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('createRefundFixtures.json'),
                array('http_code' => 200)
            ));

        $refundRequest = CreateRefundRequest::builder()->setPaymentId('1ddd77af-0bd7-500d-895b-c475c55fdefc')->setAmount(123)->build();

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createRefund($refundRequest, 123);

        $this->assertTrue($response instanceof CreateRefundResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('createRefundFixtures.json'),
                array('http_code' => 200)
            ));

        $refundRequest = array(
            'payment_id' => '1ddd77af-0bd7-500d-895b-c475c55fdefc',
            'amount' => array(
                'value' => 321,
                'currency' => 'RUB',
            )
        );

        $apiClient = new Client();

        $response = $apiClient
            ->setMaxRequestAttempts(2)
            ->setRetryTimeout(1000)
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createRefund($refundRequest);

        $this->assertTrue($response instanceof CreateRefundResponse);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"type":"error","code":"request_accepted","retry_after":1800}',
                array('http_code' => 202)
            ));

        try {
            $response = $apiClient
                ->setApiClient($curlClientStub)
                ->setAuth('shopId', 'shopPassword')
                ->createRefund($refundRequest, 123);
        } catch (ApiException $e) {
            self::assertInstanceOf('YandexCheckout\Common\Exceptions\ResponseProcessingException', $e);
            return;
        }

        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidCreateRefund($httpCode, $errorResponse, $requiredException)
    {
        $refundRequest = CreateRefundRequest::builder()->setPaymentId('1ddd77af-0bd7-500d-895b-c475c55fdefc')->setAmount(123)->build();
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->createRefund($refundRequest, 123);
        } catch (ApiException $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider paymentInfoDataProvider
     *
     * @param mixed $refundId
     * @param string $exceptionClassName
     */
    public function testRefundInfo($refundId, $exceptionClassName = null)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($exceptionClassName === null ? self::once() : self::never())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $this->getFixtures('refundInfoFixtures.json'),
                array('http_code' => 200)
            ));

        if ($exceptionClassName !== null) {
            $this->setExpectedException($exceptionClassName);
        }

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getRefundInfo($refundId);

        $this->assertTrue($response instanceof RefundResponse);

        try {
            $apiClient->getRefundInfo(null);
        } catch (\InvalidArgumentException $e) {
            // it's ok
            return;
        }
        self::fail('Exception not thrown');
    }

    /**
     * @dataProvider errorResponseDataProvider
     * @param $httpCode
     * @param $errorResponse
     * @param $requiredException
     */
    public function testInvalidRefundInfo($httpCode, $errorResponse, $requiredException)
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                $errorResponse,
                array('http_code' => $httpCode)
            ));

        $apiClient = new Client();
        $apiClient->setApiClient($curlClientStub)->setAuth('shopId', 'shopPassword');
        try {
            $apiClient->getRefundInfo(\YandexCheckout\Helpers\Random::str(36));
        } catch (ApiException $e) {
            self::assertInstanceOf($requiredException, $e);
            return;
        }
        self::fail('Exception not thrown');
    }

    public function testApiException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YandexCheckout\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                'unknown response here',
                array('http_code' => 444)
            ));
        $this->setExpectedException('YandexCheckout\Common\Exceptions\ApiException');

        $apiClient = new Client();
        $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testBadRequestException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YandexCheckout\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg", "code": "error_code", "parameter_name": "parameter_name"}',
                array('http_code' => 400)
            ));
        $this->setExpectedException('YandexCheckout\Common\Exceptions\BadApiRequestException');

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testTechnicalErrorException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YandexCheckout\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg", "code": "error_code"}',
                array('http_code' => 500)
            ));
        $this->setExpectedException('YandexCheckout\Common\Exceptions\InternalServerError');

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testUnauthorizedException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YandexCheckout\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg"}',
                array('http_code' => 401)
            ));
        $this->setExpectedException('YandexCheckout\Common\Exceptions\UnauthorizedException');

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testForbiddenException()
    {
        $payment = CreatePaymentRequest::builder()
            ->setAmount(123)
            ->setPaymentToken(\YandexCheckout\Helpers\Random::str(36))
            ->build();

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg","error_code": "error_code", "parameter_name": "parameter_name", "operation_name": "operation_name"}',
                array('http_code' => 403)
            ));
        $this->setExpectedException('YandexCheckout\Common\Exceptions\ForbiddenException');

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->createPayment($payment, 123);
    }

    public function testNotFoundException()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg","error_code": "error_code", "parameter_name": "parameter_name", "operation_name": "operation_name"}',
                array('http_code' => 404)
            ));
        $this->setExpectedException('YandexCheckout\Common\Exceptions\NotFoundException');

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentInfo(\YandexCheckout\Helpers\Random::str(36));
    }

    public function testToManyRequestsException()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"description": "error_msg","error_code": "error_code", "parameter_name": "parameter_name", "operation_name": "operation_name"}',
                array('http_code' => 429)
            ));
        $this->setExpectedException('YandexCheckout\Common\Exceptions\TooManyRequestsException');

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentInfo(\YandexCheckout\Helpers\Random::str(36));
    }

    public function testAnotherExceptions()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{}',
                array('http_code' => 322)
            ));

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentInfo(Random::str(36));

        self::assertNull($response);

        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects($this->any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{}',
                array('http_code' => 402)
            ));

        $apiClient = new Client();

        $this->setExpectedException('YandexCheckout\Common\Exceptions\ApiException');

        $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentInfo(Random::str(36));
        
    }

    public function testConfig()
    {
        $apiClient = new Client();
        $apiClient->setConfig(array(
            'url' => 'test'
        ));

        $this->assertEquals(array('url' => 'test'), $apiClient->getConfig());
    }

    public function testSetLogger()
    {
        $wrapped = new ArrayLogger();
        $logger = new \YandexCheckout\Common\LoggerWrapper($wrapped);

        $apiClient = new Client();
        $apiClient->setLogger($logger);

        $clientMock = $this->getMockBuilder('YandexCheckout\Client\ApiClientInterface')
            ->setMethods(array('setLogger', 'setConfig', 'call'))
            ->disableOriginalConstructor()
            ->getMock();
        $expectedLoggers = array();
        $clientMock->expects(self::exactly(3))->method('setLogger')->willReturnCallback(function ($logger) use(&$expectedLoggers) {
            $expectedLoggers[] = $logger;
        });
        $clientMock->expects(self::once())->method('setConfig')->willReturn($clientMock);

        $apiClient->setApiClient($clientMock);
        self::assertSame($expectedLoggers[0], $logger);

        $apiClient->setLogger($wrapped);
        $apiClient->setLogger(function ($level, $log, $context = array()) use ($wrapped) {
            $wrapped->log($level, $log, $context);
        });
    }

    public function testDecodeInvalidData()
    {
        $curlClientStub = $this->getCurlClientStub();
        $curlClientStub
            ->expects(self::any())
            ->method('sendRequest')
            ->willReturn(array(
                array('Header-Name' => 'HeaderValue'),
                '{"invalid":"json"',
                array('http_code' => 200)
            ));
        $this->setExpectedException('YandexCheckout\Common\Exceptions\JsonException');

        $apiClient = new Client();
        $response = $apiClient
            ->setApiClient($curlClientStub)
            ->setAuth('shopId', 'shopPassword')
            ->getPaymentInfo(Random::str(36));
    }

    public function testEncodeInvalidData()
    {
        $instance = new TestClient();

        if(version_compare(PHP_VERSION, '5.5') >= 0) {
            $value = array('test' => 'test', 'val' => null);
            $value['val'] = &$value;
            try {
                $instance->encode($value);
                self::fail('Exception not thrown');
            } catch (JsonException $e) {
                self::assertEquals(JSON_ERROR_RECURSION, $e->getCode());
                self::assertEquals('Failed serialize json. Unknown error', $e->getMessage());
            }
        }

        $value = array('test' => iconv('utf-8', 'windows-1251', 'абвгдеёжз'));
        try {
            $instance->encode($value);
            self::fail('Exception not thrown');
        } catch (JsonException $e) {
            self::assertEquals(JSON_ERROR_UTF8, $e->getCode());
            self::assertEquals('Failed serialize json. Malformed UTF-8 characters, possibly incorrectly encoded', $e->getMessage());
        }
    }

    public function testSdkVersion()
    {
        $composerJsonFile = dirname(__FILE__) . '/../../composer.json';
        $data = json_decode(file_get_contents($composerJsonFile));
        self::assertEquals($data->version, Client::SDK_VERSION);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getCurlClientStub()
    {
        $clientStub = $this->getMockBuilder('YandexCheckout\Client\CurlClient')
            ->setMethods(array('sendRequest'))
            ->getMock();

        return $clientStub;
    }

    public function errorResponseDataProvider()
    {
        return array(
            array(\YandexCheckout\Common\Exceptions\BadApiRequestException::HTTP_CODE, '{}', 'YandexCheckout\Common\Exceptions\BadApiRequestException'),
            array(\YandexCheckout\Common\Exceptions\ForbiddenException::HTTP_CODE, '{}', 'YandexCheckout\Common\Exceptions\ForbiddenException'),
            array(\YandexCheckout\Common\Exceptions\UnauthorizedException::HTTP_CODE, '{}', 'YandexCheckout\Common\Exceptions\UnauthorizedException'),
            array(\YandexCheckout\Common\Exceptions\InternalServerError::HTTP_CODE, '{}', 'YandexCheckout\Common\Exceptions\InternalServerError'),
        );
    }

    /**
     * @return bool|string
     */
    private function getFixtures($fileName)
    {
        return file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $fileName);
    }
}

class ArrayLogger
{
    private $lastLog;

    public function log($level, $message, $context)
    {
        $this->lastLog = array($level, $message, $context);
    }

    public function getLastLog()
    {
        return $this->lastLog;
    }
}

class TestClient extends Client
{
    public function encode($data)
    {
        $refl = new \ReflectionMethod($this, 'encodeData');
        $refl->setAccessible(true);
        return $refl->invoke($this, $data);
    }
}