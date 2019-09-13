<?php


namespace Okay\Core;


use Okay\Core\Adapters\Response\AdapterManager;
use OkayLicense\License;

class Response
{

    private $content = [];
    private $adapterManager;
    private $headers;
    private $type;
    private $license;
    private $statusCode = 200;
    
    public function __construct(AdapterManager $adapterManager, License $license)
    {
        $this->adapterManager = $adapterManager;
        $this->license = $license;
        $this->type = RESPONSE_HTML;
    }

    /**
     * Метод перенаправления на другой ресурс. При помощи функции exit() 
     * прекращает исполнение кода расположенного ниже вызова метода.
     * 
     * @param $resource
     * @param $responseCode
     * 
     * @return void
     * @throws \Exception
     */
    public function redirectTo($resource, $responseCode = 302)
    {
        $responseCode = (int) $responseCode;

        if (!in_array($responseCode, [301, 302, 307, 308])) {
            throw new \Exception("$responseCode is not valid redirect response code.");
        }

        $headerContent = 'location: '.$resource;
        header($headerContent, false, $responseCode);
        exit();
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function addHeader($headerContent, $replace = true, $responseCode = null)
    {
        $this->headers[] = [$headerContent, $replace, $responseCode];
        return $this;
    }
    
    public function setContent($content, $type = null)
    {
        if ($type !== null) {
            $this->type = trim($type);
        }
        $this->content[] = $content;
        return $this;
    }
    
    public function setContentType($type)
    {
        $this->type = trim($type);
    }
    
    public function getContentType()
    {
        return $this->type;
    }
    
    public function getContent()
    {
        return implode('', $this->content);
    }
    
    public function sendContent()
    {
        /** @var Adapters\Response\AbstractResponse $adapter */
        $adapter = $this->adapterManager->getAdapter($this->type);

        if ($this->headersExists()) {
            $this->sendHeaders();
        }

        $this->license->setResponseType($this->type);
        
        $adapter->send($this->getContent());
    }

    public function sendHeaders()
    {
        $this->commitStatusCode();

        foreach ($this->headers as $header) {
            list($headerContent, $replace, $responseCode) = $header;

            if (is_null($responseCode)) {
                header($headerContent, $replace);
                continue;
            }

            header($headerContent, $replace, $responseCode);
        }
    }

    private function headersExists()
    {
        if (!empty($this->headers)) {
            return true;
        }

        return false;
    }

    public function setHeaderLastModify($lastModify)
    {
        $lastModify = empty($lastModify) ? date("Y-m-d H:i:s") : $lastModify;
        $tmpDate = date_parse($lastModify);
        @$lastModifiedUnix = mktime( $tmpDate['hour'], $tmpDate['minute'], $tmpDate['second '], $tmpDate['month'],$tmpDate['day'],$tmpDate['year'] );

        //Проверка модификации страницы
        $lastModified = gmdate("D, d M Y H:i:s \G\M\T", $lastModifiedUnix);
        $ifModifiedSince = false;

        if (isset($_ENV['HTTP_IF_MODIFIED_SINCE'])) {
            $ifModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));
        }

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $ifModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
        }

        if ($ifModifiedSince && $ifModifiedSince >= $lastModifiedUnix) {
            $this->setStatusCode(304)->sendHeaders();
            exit;
        }

        $this->addHeader('Last-Modified: '. $lastModified);
    }

    private function commitStatusCode()
    {
        if (empty($this->statusCode)) {
            throw new \Exception('Response status code cannot be empty');
        }

        switch ($this->statusCode) {
            // 4XX
            case 423:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 423 Locked');
                break;
            case 417:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 417 Expectation Failed');
                break;
            case 410:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 410 Gone');
                break;
            case 405:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 405 Method Not Allowed');
                break;
            case 404:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
                break;
            case 401:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
                break;
            case 400:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
                break;

            // 3XX
            case 308:
                throw new \Exception('Please use Okay\Core\Response::redirectTo method for 308 redirect');
                break;
            case 307:
                throw new \Exception('Please use Okay\Core\Response::redirectTo method for 307 redirect');
                break;
            case 304:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 304 Not Modified');
                break;
            case 302:
                throw new \Exception('Please use Okay\Core\Response::redirectTo method for 302 redirect');
                break;
            case 301:
                throw new \Exception('Please use Okay\Core\Response::redirectTo method for 301 redirect');
                break;

            // 2XX
            case 206:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 206 Partial Content');
                break;
            case 205:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 205 Reset Content');
                break;
            case 200:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 200 OK');
                break;

            // 1XX
            case 102:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 100 Continue');
                break;
            case 101:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 101 Switching Protocols');
                break;
            case 100:
                $this->addHeader($_SERVER['SERVER_PROTOCOL'].' 102 Processing');
                break;

            default:
                throw new \Exception('Response status code "'.$this->statusCode.'" incorrect or don`t supported');
        }
    }
}
