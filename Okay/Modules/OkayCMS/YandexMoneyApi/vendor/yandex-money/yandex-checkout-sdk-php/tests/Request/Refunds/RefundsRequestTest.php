<?php

namespace Tests\YandexCheckout\Request\Refunds;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Helpers\Random;
use YandexCheckout\Helpers\StringObject;
use YandexCheckout\Model\RefundStatus;
use YandexCheckout\Request\Refunds\RefundsRequest;
use YandexCheckout\Request\Refunds\RefundsRequestBuilder;

class RefundsRequestTest extends TestCase
{
    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testRefundId($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasRefundId());
        self::assertNull($instance->getRefundId());
        self::assertNull($instance->refundId);

        $instance->setRefundId($options['refund_id']);
        if (empty($options['refund_id'])) {
            self::assertFalse($instance->hasRefundId());
            self::assertNull($instance->getRefundId());
            self::assertNull($instance->refundId);
        } else {
            self::assertTrue($instance->hasRefundId());
            self::assertEquals($options['refund_id'], $instance->getRefundId());
            self::assertEquals($options['refund_id'], $instance->refundId);
        }

        $instance->setRefundId('');
        self::assertFalse($instance->hasRefundId());
        self::assertNull($instance->getRefundId());
        self::assertNull($instance->refundId);

        $instance->refundId = $options['refund_id'];
        if (empty($options['refund_id'])) {
            self::assertFalse($instance->hasRefundId());
            self::assertNull($instance->getRefundId());
            self::assertNull($instance->refundId);
        } else {
            self::assertTrue($instance->hasRefundId());
            self::assertEquals($options['refund_id'], $instance->getRefundId());
            self::assertEquals($options['refund_id'], $instance->refundId);
        }
    }

    /**
     * @dataProvider invalidPaymentIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidRefundId($value)
    {
        $instance = new RefundsRequest();
        $instance->setRefundId($value);
    }

    /**
     * @dataProvider invalidPaymentIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidRefundId($value)
    {
        $instance = new RefundsRequest();
        $instance->refundId = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testPaymentId($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasPaymentId());
        self::assertNull($instance->getPaymentId());
        self::assertNull($instance->paymentId);

        $instance->setPaymentId($options['payment_id']);
        if (empty($options['payment_id'])) {
            self::assertFalse($instance->hasPaymentId());
            self::assertNull($instance->getPaymentId());
            self::assertNull($instance->paymentId);
        } else {
            self::assertTrue($instance->hasPaymentId());
            self::assertEquals($options['payment_id'], $instance->getPaymentId());
            self::assertEquals($options['payment_id'], $instance->paymentId);
        }

        $instance->setPaymentId('');
        self::assertFalse($instance->hasPaymentId());
        self::assertNull($instance->getPaymentId());
        self::assertNull($instance->paymentId);

        $instance->paymentId = $options['payment_id'];
        if (empty($options['payment_id'])) {
            self::assertFalse($instance->hasPaymentId());
            self::assertNull($instance->getPaymentId());
            self::assertNull($instance->paymentId);
        } else {
            self::assertTrue($instance->hasPaymentId());
            self::assertEquals($options['payment_id'], $instance->getPaymentId());
            self::assertEquals($options['payment_id'], $instance->paymentId);
        }
    }

    /**
     * @dataProvider invalidPaymentIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidPaymentId($value)
    {
        $instance = new RefundsRequest();
        $instance->setPaymentId($value);
    }

    /**
     * @dataProvider invalidPaymentIdDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidPaymentId($value)
    {
        $instance = new RefundsRequest();
        $instance->paymentId = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAccountId($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasAccountId());
        self::assertNull($instance->getAccountId());
        self::assertNull($instance->accountId);

        $instance->setAccountId($options['account_id']);
        if (empty($options['account_id'])) {
            self::assertFalse($instance->hasAccountId());
            self::assertNull($instance->getAccountId());
            self::assertNull($instance->accountId);
        } else {
            self::assertTrue($instance->hasAccountId());
            self::assertEquals($options['account_id'], $instance->getAccountId());
            self::assertEquals($options['account_id'], $instance->accountId);
        }

        $instance->setAccountId('');
        self::assertFalse($instance->hasAccountId());
        self::assertNull($instance->getAccountId());
        self::assertNull($instance->accountId);

        $instance->accountId = $options['account_id'];
        if (empty($options['account_id'])) {
            self::assertFalse($instance->hasAccountId());
            self::assertNull($instance->getAccountId());
            self::assertNull($instance->accountId);
        } else {
            self::assertTrue($instance->hasAccountId());
            self::assertEquals($options['account_id'], $instance->getAccountId());
            self::assertEquals($options['account_id'], $instance->accountId);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider validStringDataProvider
     * @param mixed $value
     */
    public function testSetInvalidAccountId($value)
    {
        $instance = new RefundsRequest();
        $instance->setAccountId($value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider validStringDataProvider
     * @param mixed $value
     */
    public function testSetterInvalidAccountId($value)
    {
        $instance = new RefundsRequest();
        $instance->accountId = $value;
    }

    /**
     * @return array
     */
    public function validStringDataProvider()
    {
        return array(
            array(array()),
            array(true),
            array(false),
            array(new \stdClass()),
        );
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testGatewayId($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasGatewayId());
        self::assertNull($instance->getGatewayId());
        self::assertNull($instance->gatewayId);

        $instance->setGatewayId($options['gateway_id']);
        if (empty($options['gateway_id'])) {
            self::assertFalse($instance->hasGatewayId());
            self::assertNull($instance->getGatewayId());
            self::assertNull($instance->gatewayId);
        } else {
            self::assertTrue($instance->hasGatewayId());
            self::assertEquals($options['gateway_id'], $instance->getGatewayId());
            self::assertEquals($options['gateway_id'], $instance->gatewayId);
        }

        $instance->setGatewayId('');
        self::assertFalse($instance->hasGatewayId());
        self::assertNull($instance->getGatewayId());
        self::assertNull($instance->gatewayId);

        $instance->gatewayId = $options['gateway_id'];
        if (empty($options['gateway_id'])) {
            self::assertFalse($instance->hasGatewayId());
            self::assertNull($instance->getGatewayId());
            self::assertNull($instance->gatewayId);
        } else {
            self::assertTrue($instance->hasGatewayId());
            self::assertEquals($options['gateway_id'], $instance->getGatewayId());
            self::assertEquals($options['gateway_id'], $instance->gatewayId);
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider validStringDataProvider
     * @param mixed $value
     */
    public function testSetInvalidGatewayId($value)
    {
        $instance = new RefundsRequest();
        $instance->setGatewayId($value);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider validStringDataProvider
     * @param mixed $value
     */
    public function testSetterInvalidGatewayId($value)
    {
        $instance = new RefundsRequest();
        $instance->gatewayId = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testCreateGte($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasCreatedGte());
        self::assertNull($instance->getCreatedGte());
        self::assertNull($instance->createdGte);

        $instance->setCreatedGte($options['create_gte']);
        if (empty($options['create_gte'])) {
            self::assertFalse($instance->hasCreatedGte());
            self::assertNull($instance->getCreatedGte());
            self::assertNull($instance->createdGte);
        } else {
            self::assertTrue($instance->hasCreatedGte());
            self::assertEquals($options['create_gte'], $instance->getCreatedGte()->format(DATE_ATOM));
            self::assertEquals($options['create_gte'], $instance->createdGte->format(DATE_ATOM));
        }

        $instance->setCreatedGte('');
        self::assertFalse($instance->hasCreatedGte());
        self::assertNull($instance->getCreatedGte());
        self::assertNull($instance->createdGte);

        $instance->createdGte = $options['create_gte'];
        if (empty($options['create_gte'])) {
            self::assertFalse($instance->hasCreatedGte());
            self::assertNull($instance->getCreatedGte());
            self::assertNull($instance->createdGte);
        } else {
            self::assertTrue($instance->hasCreatedGte());
            self::assertEquals($options['create_gte'], $instance->getCreatedGte()->format(DATE_ATOM));
            self::assertEquals($options['create_gte'], $instance->createdGte->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCreatedGte($value)
    {
        $instance = new RefundsRequest();
        $instance->setCreatedGte($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCreatedGte($value)
    {
        $instance = new RefundsRequest();
        $instance->createdGte = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testCreateGt($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasCreatedGt());
        self::assertNull($instance->getCreatedGt());
        self::assertNull($instance->createdGt);

        $instance->setCreatedGt($options['create_gt']);
        if (empty($options['create_gt'])) {
            self::assertFalse($instance->hasCreatedGte());
            self::assertNull($instance->getCreatedGte());
            self::assertNull($instance->createdGte);
        } else {
            self::assertTrue($instance->hasCreatedGt());
            self::assertEquals($options['create_gt'], $instance->getCreatedGt()->format(DATE_ATOM));
            self::assertEquals($options['create_gt'], $instance->createdGt->format(DATE_ATOM));
        }

        $instance->setCreatedGt('');
        self::assertFalse($instance->hasCreatedGt());
        self::assertNull($instance->getCreatedGt());
        self::assertNull($instance->createdGt);

        $instance->createdGt = $options['create_gt'];
        if (empty($options['create_gt'])) {
            self::assertFalse($instance->hasCreatedGt());
            self::assertNull($instance->getCreatedGt());
            self::assertNull($instance->createdGt);
        } else {
            self::assertTrue($instance->hasCreatedGt());
            self::assertEquals($options['create_gt'], $instance->getCreatedGt()->format(DATE_ATOM));
            self::assertEquals($options['create_gt'], $instance->createdGt->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCreatedGt($value)
    {
        $instance = new RefundsRequest();
        $instance->setCreatedGt($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCreatedGt($value)
    {
        $instance = new RefundsRequest();
        $instance->createdGt = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testCreateLte($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasCreatedLte());
        self::assertNull($instance->getCreatedLte());
        self::assertNull($instance->createdLte);

        $instance->setCreatedLte($options['create_lte']);
        if (empty($options['create_lte'])) {
            self::assertFalse($instance->hasCreatedLte());
            self::assertNull($instance->getCreatedLte());
            self::assertNull($instance->createdLte);
        } else {
            self::assertTrue($instance->hasCreatedLte());
            self::assertEquals($options['create_lte'], $instance->getCreatedLte()->format(DATE_ATOM));
            self::assertEquals($options['create_lte'], $instance->createdLte->format(DATE_ATOM));
        }

        $instance->setCreatedLte('');
        self::assertFalse($instance->hasCreatedLte());
        self::assertNull($instance->getCreatedLte());
        self::assertNull($instance->createdLte);

        $instance->createdLte = $options['create_lte'];
        if (empty($options['create_lte'])) {
            self::assertFalse($instance->hasCreatedLte());
            self::assertNull($instance->getCreatedLte());
            self::assertNull($instance->createdLte);
        } else {
            self::assertTrue($instance->hasCreatedLte());
            self::assertEquals($options['create_lte'], $instance->getCreatedLte()->format(DATE_ATOM));
            self::assertEquals($options['create_lte'], $instance->createdLte->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCreatedLte($value)
    {
        $instance = new RefundsRequest();
        $instance->setCreatedLte($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCreatedLte($value)
    {
        $instance = new RefundsRequest();
        $instance->createdLte = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testCreateLt($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasCreatedLt());
        self::assertNull($instance->getCreatedLt());
        self::assertNull($instance->createdLt);

        $instance->setCreatedLt($options['create_lt']);
        if (empty($options['create_lt'])) {
            self::assertFalse($instance->hasCreatedLt());
            self::assertNull($instance->getCreatedLt());
            self::assertNull($instance->createdLt);
        } else {
            self::assertTrue($instance->hasCreatedLt());
            self::assertEquals($options['create_lt'], $instance->getCreatedLt()->format(DATE_ATOM));
            self::assertEquals($options['create_lt'], $instance->createdLt->format(DATE_ATOM));
        }

        $instance->setCreatedLt('');
        self::assertFalse($instance->hasCreatedLt());
        self::assertNull($instance->getCreatedLt());
        self::assertNull($instance->createdLt);

        $instance->createdLt = $options['create_lt'];
        if (empty($options['create_lt'])) {
            self::assertFalse($instance->hasCreatedLt());
            self::assertNull($instance->getCreatedLt());
            self::assertNull($instance->createdLt);
        } else {
            self::assertTrue($instance->hasCreatedLt());
            self::assertEquals($options['create_lt'], $instance->getCreatedLt()->format(DATE_ATOM));
            self::assertEquals($options['create_lt'], $instance->createdLt->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCreatedLt($value)
    {
        $instance = new RefundsRequest();
        $instance->setCreatedLt($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCreatedLt($value)
    {
        $instance = new RefundsRequest();
        $instance->createdLt = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAuthorizedGte($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasAuthorizedGte());
        self::assertNull($instance->getAuthorizedGte());
        self::assertNull($instance->authorizedGte);

        $instance->setAuthorizedGte($options['authorized_gte']);
        if (empty($options['authorized_gte'])) {
            self::assertFalse($instance->hasAuthorizedGte());
            self::assertNull($instance->getAuthorizedGte());
            self::assertNull($instance->authorizedGte);
        } else {
            self::assertTrue($instance->hasAuthorizedGte());
            self::assertEquals($options['authorized_gte'], $instance->getAuthorizedGte()->format(DATE_ATOM));
            self::assertEquals($options['authorized_gte'], $instance->authorizedGte->format(DATE_ATOM));
        }

        $instance->setAuthorizedGte('');
        self::assertFalse($instance->hasAuthorizedGte());
        self::assertNull($instance->getAuthorizedGte());
        self::assertNull($instance->authorizedGte);

        $instance->authorizedGte = $options['authorized_gte'];
        if (empty($options['authorized_gte'])) {
            self::assertFalse($instance->hasAuthorizedGte());
            self::assertNull($instance->getAuthorizedGte());
            self::assertNull($instance->authorizedGte);
        } else {
            self::assertTrue($instance->hasAuthorizedGte());
            self::assertEquals($options['authorized_gte'], $instance->getAuthorizedGte()->format(DATE_ATOM));
            self::assertEquals($options['authorized_gte'], $instance->authorizedGte->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidAuthorizedGte($value)
    {
        $instance = new RefundsRequest();
        $instance->setAuthorizedGte($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidAuthorizedGte($value)
    {
        $instance = new RefundsRequest();
        $instance->authorizedGte = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAuthorizedGt($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasAuthorizedGt());
        self::assertNull($instance->getAuthorizedGt());
        self::assertNull($instance->authorizedGt);

        $instance->setAuthorizedGt($options['authorized_gt']);
        if (empty($options['authorized_gt'])) {
            self::assertFalse($instance->hasAuthorizedGt());
            self::assertNull($instance->getAuthorizedGt());
            self::assertNull($instance->authorizedGt);
        } else {
            self::assertTrue($instance->hasAuthorizedGt());
            self::assertEquals($options['authorized_gt'], $instance->getAuthorizedGt()->format(DATE_ATOM));
            self::assertEquals($options['authorized_gt'], $instance->authorizedGt->format(DATE_ATOM));
        }

        $instance->setAuthorizedGt('');
        self::assertFalse($instance->hasAuthorizedGt());
        self::assertNull($instance->getAuthorizedGt());
        self::assertNull($instance->authorizedGt);

        $instance->authorizedGt = $options['authorized_gt'];
        if (empty($options['authorized_gt'])) {
            self::assertFalse($instance->hasAuthorizedGt());
            self::assertNull($instance->getAuthorizedGt());
            self::assertNull($instance->authorizedGt);
        } else {
            self::assertTrue($instance->hasAuthorizedGt());
            self::assertEquals($options['authorized_gt'], $instance->getAuthorizedGt()->format(DATE_ATOM));
            self::assertEquals($options['authorized_gt'], $instance->authorizedGt->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidAuthorizedGt($value)
    {
        $instance = new RefundsRequest();
        $instance->setAuthorizedGt($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidAuthorizedGt($value)
    {
        $instance = new RefundsRequest();
        $instance->authorizedGt = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAuthorizedLte($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasAuthorizedLte());
        self::assertNull($instance->getAuthorizedLte());
        self::assertNull($instance->authorizedLte);

        $instance->setAuthorizedLte($options['authorized_lte']);
        if (empty($options['authorized_lte'])) {
            self::assertFalse($instance->hasAuthorizedLte());
            self::assertNull($instance->getAuthorizedLte());
            self::assertNull($instance->authorizedLte);
        } else {
            self::assertTrue($instance->hasAuthorizedLte());
            self::assertEquals($options['authorized_lte'], $instance->getAuthorizedLte()->format(DATE_ATOM));
            self::assertEquals($options['authorized_lte'], $instance->authorizedLte->format(DATE_ATOM));
        }

        $instance->setAuthorizedLte('');
        self::assertFalse($instance->hasAuthorizedLte());
        self::assertNull($instance->getAuthorizedLte());
        self::assertNull($instance->authorizedLte);

        $instance->authorizedLte = $options['authorized_lte'];
        if (empty($options['authorized_lte'])) {
            self::assertFalse($instance->hasAuthorizedLte());
            self::assertNull($instance->getAuthorizedLte());
            self::assertNull($instance->authorizedLte);
        } else {
            self::assertTrue($instance->hasAuthorizedLte());
            self::assertEquals($options['authorized_lte'], $instance->getAuthorizedLte()->format(DATE_ATOM));
            self::assertEquals($options['authorized_lte'], $instance->authorizedLte->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidAuthorizedLte($value)
    {
        $instance = new RefundsRequest();
        $instance->setAuthorizedLte($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidAuthorizedLte($value)
    {
        $instance = new RefundsRequest();
        $instance->authorizedLte = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testAuthorizedLt($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasAuthorizedLt());
        self::assertNull($instance->getAuthorizedLt());
        self::assertNull($instance->authorizedLt);

        $instance->setAuthorizedLt($options['authorized_lt']);
        if (empty($options['authorized_lt'])) {
            self::assertFalse($instance->hasAuthorizedLt());
            self::assertNull($instance->getAuthorizedLt());
            self::assertNull($instance->authorizedLt);
        } else {
            self::assertTrue($instance->hasAuthorizedLt());
            self::assertEquals($options['authorized_lt'], $instance->getAuthorizedLt()->format(DATE_ATOM));
            self::assertEquals($options['authorized_lt'], $instance->authorizedLt->format(DATE_ATOM));
        }

        $instance->setAuthorizedLt('');
        self::assertFalse($instance->hasAuthorizedLt());
        self::assertNull($instance->getAuthorizedLt());
        self::assertNull($instance->authorizedLt);

        $instance->authorizedLt = $options['authorized_lt'];
        if (empty($options['authorized_lt'])) {
            self::assertFalse($instance->hasAuthorizedLt());
            self::assertNull($instance->getAuthorizedLt());
            self::assertNull($instance->authorizedLt);
        } else {
            self::assertTrue($instance->hasAuthorizedLt());
            self::assertEquals($options['authorized_lt'], $instance->getAuthorizedLt()->format(DATE_ATOM));
            self::assertEquals($options['authorized_lt'], $instance->authorizedLt->format(DATE_ATOM));
        }
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidAuthorizedLt($value)
    {
        $instance = new RefundsRequest();
        $instance->setAuthorizedLt($value);
    }

    /**
     * @dataProvider invalidDateDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidAuthorizedLt($value)
    {
        $instance = new RefundsRequest();
        $instance->authorizedLt = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testStatus($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasStatus());
        self::assertNull($instance->getStatus());
        self::assertNull($instance->status);

        $instance->setStatus($options['status']);
        if (empty($options['status'])) {
            self::assertFalse($instance->hasStatus());
            self::assertNull($instance->getStatus());
            self::assertNull($instance->status);
        } else {
            self::assertTrue($instance->hasStatus());
            self::assertEquals($options['status'], $instance->getStatus());
            self::assertEquals($options['status'], $instance->status);
        }

        $instance->setStatus('');
        self::assertFalse($instance->hasStatus());
        self::assertNull($instance->getStatus());
        self::assertNull($instance->status);

        $instance->status = $options['status'];
        if (empty($options['status'])) {
            self::assertFalse($instance->hasStatus());
            self::assertNull($instance->getStatus());
            self::assertNull($instance->status);
        } else {
            self::assertTrue($instance->hasStatus());
            self::assertEquals($options['status'], $instance->getStatus());
            self::assertEquals($options['status'], $instance->status);
        }
    }

    /**
     * @dataProvider invalidStatusDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidStatus($value)
    {
        $instance = new RefundsRequest();
        $instance->setStatus($value);
    }

    /**
     * @dataProvider invalidStatusDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidStatus($value)
    {
        $instance = new RefundsRequest();
        $instance->status = $value;
    }

    /**
     * @dataProvider validDataProvider
     * @param $options
     */
    public function testNextPage($options)
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->hasNextPage());
        self::assertNull($instance->getNextPage());
        self::assertNull($instance->nextPage);

        $instance->setNextPage($options['next_page']);
        if (empty($options['next_page'])) {
            self::assertFalse($instance->hasNextPage());
            self::assertNull($instance->getNextPage());
            self::assertNull($instance->nextPage);
        } else {
            self::assertTrue($instance->hasNextPage());
            self::assertEquals($options['next_page'], $instance->getNextPage());
            self::assertEquals($options['next_page'], $instance->nextPage);
        }

        $instance->setNextPage('');
        self::assertFalse($instance->hasNextPage());
        self::assertNull($instance->getNextPage());
        self::assertNull($instance->nextPage);

        $instance->nextPage = $options['next_page'];
        if (empty($options['next_page'])) {
            self::assertFalse($instance->hasNextPage());
            self::assertNull($instance->getNextPage());
            self::assertNull($instance->nextPage);
        } else {
            self::assertTrue($instance->hasNextPage());
            self::assertEquals($options['next_page'], $instance->getNextPage());
            self::assertEquals($options['next_page'], $instance->nextPage);
        }
    }

    /**
     * @dataProvider invalidNextPageDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetInvalidNextPage($value)
    {
        $instance = new RefundsRequest();
        $instance->setNextPage($value);
    }

    /**
     * @dataProvider invalidNextPageDataProvider
     * @expectedException \InvalidArgumentException
     * @param $value
     */
    public function testSetterInvalidNextPage($value)
    {
        $instance = new RefundsRequest();
        $instance->nextPage = $value;
    }

    public function testValidate()
    {
        $instance = new RefundsRequest();

        self::assertFalse($instance->validate());
        $instance->setAccountId(Random::str(10));
        self::assertTrue($instance->validate());
        $instance->setAccountId(null);
        self::assertFalse($instance->validate());
    }

    public function testBuilder()
    {
        $builder = RefundsRequest::builder();
        self::assertTrue($builder instanceof RefundsRequestBuilder);
    }

    public function validDataProvider()
    {
        $result = array(
            array(
                array(
                    'refund_id' => null,
                    'payment_id' => null,
                    'account_id' => null,
                    'gateway_id' => null,
                    'create_gte' => null,
                    'create_gt' => null,
                    'create_lte' => null,
                    'create_lt' => null,
                    'authorized_gte' => null,
                    'authorized_gt' => null,
                    'authorized_lte' => null,
                    'authorized_lt' => null,
                    'status' => null,
                    'next_page' => null,
                ),
            ),
            array(
                array(
                    'refund_id' => null,
                    'payment_id' => '',
                    'account_id' => '',
                    'gateway_id' => '',
                    'create_gte' => '',
                    'create_gt' => '',
                    'create_lte' => '',
                    'create_lt' => '',
                    'authorized_gte' => '',
                    'authorized_gt' => '',
                    'authorized_lte' => '',
                    'authorized_lt' => '',
                    'status' => '',
                    'next_page' => '',
                ),
            ),
        );
        $statuses = RefundStatus::getValidValues();
        for ($i = 0; $i < 10; $i++) {
            $request = array(
                'refund_id' => $this->randomString(36),
                'payment_id' => $this->randomString(36),
                'account_id'    => uniqid(),
                'gateway_id' => uniqid(),
                'create_gte' => date(DATE_ATOM, mt_rand(1, time())),
                'create_gt' => date(DATE_ATOM, mt_rand(1, time())),
                'create_lte' => date(DATE_ATOM, mt_rand(1, time())),
                'create_lt' => date(DATE_ATOM, mt_rand(1, time())),
                'authorized_gte' => date(DATE_ATOM, mt_rand(1, time())),
                'authorized_gt' => date(DATE_ATOM, mt_rand(1, time())),
                'authorized_lte' => date(DATE_ATOM, mt_rand(1, time())),
                'authorized_lt' => date(DATE_ATOM, mt_rand(1, time())),
                'status' => $statuses[mt_rand(0, count($statuses) - 1)],
                'next_page' => uniqid(),
            );
            $result[] = array($request);
        }
        return $result;
    }

    public function invalidStatusDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
            array(Random::str(1, 10)),
            array(new StringObject(Random::str(1, 10))),
        );
    }

    public function invalidNextPageDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
        );
    }

    public function invalidPaymentIdDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
            array(Random::str(35)),
            array(Random::str(37)),
            array(new StringObject(Random::str(10))),
        );
    }

    public function invalidDateDataProvider()
    {
        return array(
            array(true),
            array(false),
            array(array()),
            array(new \stdClass()),
            array(Random::str(35)),
            array(Random::str(37)),
            array(new StringObject(Random::str(10))),
            array(-123),
        );
    }

    private function randomString($length, $any = true)
    {
        static $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-+_.';

        $result = '';
        for ($i = 0; $i < $length; $i++) {
            if ($any) {
                $char = chr(mt_rand(32, 126));
            } else {
                $rnd = mt_rand(0, strlen($chars) - 1);
                $char = substr($chars, $rnd, 1);
            }
            $result .= $char;
        }
        return $result;
    }
}