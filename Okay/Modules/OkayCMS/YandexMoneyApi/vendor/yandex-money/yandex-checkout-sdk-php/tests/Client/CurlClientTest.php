<?php

namespace Tests\YandexCheckout\Client;

use PHPUnit\Framework\TestCase;
use YandexCheckout\Client\CurlClient;
use YandexCheckout\Common\HttpVerb;

require_once __DIR__ . '/ClientTest.php';

class CurlClientTest extends TestCase
{
    public function testConnectionTimeout()
    {
        $client = new CurlClient();
        $client->setConnectionTimeout(10);
        $this->assertEquals(10, $client->getConnectionTimeout());
    }

    public function testTimeout()
    {
        $client = new CurlClient();
        $client->setTimeout(10);
        $this->assertEquals(10, $client->getTimeout());
    }

    /**
     * @dataProvider curlErrorCodeProvider
     * @expectedException \YandexCheckout\Common\Exceptions\ApiConnectionException
     */
    public function testHandleCurlError($error, $errn)
    {
        $client    = new CurlClient();
        $reflector = new \ReflectionClass('\YandexCheckout\Client\CurlClient');
        $method    = $reflector->getMethod('handleCurlError');
        $method->setAccessible(true);
        $method->invokeArgs($client, array($error, $errn));
    }

    public function testConfig()
    {
        $config = array('url' => 'test:url');
        $client = new CurlClient();
        $client->setConfig($config);
        $this->assertEquals($config, $client->getConfig());
    }

    public function testCloseConnection()
    {
        $wrapped        = new ArrayLogger();
        $logger         = new \YandexCheckout\Common\LoggerWrapper($wrapped);
        $curlClientMock = $this->getMockBuilder('YandexCheckout\Client\CurlClient')
                               ->setMethods(array('closeCurlConnection', 'sendRequest'))
                               ->getMock();
        $curlClientMock->setLogger($logger);
        $curlClientMock->setKeepAlive(false);
        $curlClientMock->setShopId(123);
        $curlClientMock->setShopPassword(234);
        $curlClientMock->expects($this->once())->method('sendRequest')->willReturn(array(
            array('Header-Name' => 'HeaderValue'),
            '{body:sample}',
            array('http_code' => 200),
        ));
        $curlClientMock->expects($this->once())->method('closeCurlConnection');
        $curlClientMock->call('', HttpVerb::HEAD, array('queryParam' => 'value'), 'testBodyValue',
            array('testHeader' => 'testValue'));
    }

    public function testAuthorizeException()
    {
        $this->setExpectedException('YandexCheckout\Common\Exceptions\AuthorizeException');
        $client = new CurlClient();
        $client->call('', HttpVerb::HEAD, array('queryParam' => 'value'), array('httpBody' => 'testValue'),
            array('testHeader' => 'testValue'));
    }

    public function testHttpVerbException()
    {
        $curlClientMock = $this->getMockBuilder('YandexCheckout\Client\CurlClient')
                               ->setMethods(array('setCurlOption'))
                               ->getMock();
        $curlClientMock->setBody(HttpVerb::OPTIONS, array());
        $curlClientMock->setBody(HttpVerb::DELETE, array());
        $curlClientMock->setBody(HttpVerb::PATCH, array());
        $curlClientMock->setBody(HttpVerb::PUT, array());
        $this->setExpectedException('YandexCheckout\Common\Exceptions\ApiException');
        $curlClientMock->setBody('invalid verb', array());
    }

    public function curlErrorCodeProvider()
    {
        return array(
            array('error message', CURLE_SSL_CACERT),
            array('error message', CURLE_COULDNT_CONNECT),
            array('error message', 0),
        );
    }
}
