<?php
function __autoload($class){
    
    $class = str_ireplace('\\', '/',$class);
    include $class.'.php';
}

$uri = new Resources\Uri;

$controller = 'Controllers\\'.ucwords( $uri->getClass() );
$method     = $uri->getMethod();

if( ! $request = $uri->getRequests() )
    $request = array();

$instance = new $controller;

if( ! method_exists($instance, $method) )
        die('Error 404 - Method not exists!');

call_user_func_array(array($instance, $method), $request);