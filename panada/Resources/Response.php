<?php

namespace Resources;

class Response
{
    public static $statusCode = 200;
    public static $headers = [
        'Content-Type' => 'text/html; charset=utf-8',
    ];
    public static $body;
    
    public function send()
    {
        self::sendHeader();
        return self::$body;
    }
    
    public function sendBody()
    {
        return self::$body;
    }
    
    public static function sendHeader()
    {
        Tools::setStatusHeader(self::$statusCode);
            
        foreach(self::$headers as $name => $value) {
            header($name.': '.$value, true);
        }
    }
    
    public function sendHeaders()
    {
        self::sendHeader();
    }
    
    public function setBody($body)
    {
        self::$body = $body;
    }
    
    public static function setHeader($key, $value, $statusCode = 200)
    {
        self::$statusCode = $statusCode;
        self::$headers[$key] = $value;
    }
    
    public function setStatusCode($statusCode)
    {
        self::$statusCode = $statusCode;
    }
}
