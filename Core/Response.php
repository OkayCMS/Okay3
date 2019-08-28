<?php


namespace Okay\Core;


use Okay\Core\Adapters\Response\AdapterManager;

class Response
{

    private $content = [];
    private $adapterManager;
    private $headers;
    private $type;
    
    public function __construct(AdapterManager $adapterManager)
    {
        $this->adapterManager = $adapterManager;
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

        if (!in_array($responseCode, [301, 302, 307])) {
            throw new \Exception("$responseCode is not valid redirect response code.");
        }

        $headerContent = 'location: '.$resource;
        header($headerContent, false, $responseCode);
        exit();
    }

    public function sendHeader404()
    {
        header('http/1.1 404 not found');
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
        
        $adapter->send($this->getContent());
    }

    public function sendHeaders()
    {
        foreach ($this->headers as $header) {
            list($headerContent, $replace, $responseCode) = $header;

            if ($responseCode === null) {
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

}
