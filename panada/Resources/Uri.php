<?php

/**
 * Handler for request and mapped into, controller, metohd and requests.
 *
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 * @link	http://panadaframework.com/
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @since	version 0.1
 * @package	Resources
 */

namespace Resources;

final class Uri {

    private
    $pathUri = array();
    public
            $baseUri,
            $defaultController;
    public static
    $staticDefaultController = 'Home';

    /**
     * Class constructor
     *
     * Difine the SAPI mode, cli or web/http
     *
     * @return void
     */
    public function __construct() {
        if (PHP_SAPI == 'cli') {
            $this->pathUri = array_slice($_SERVER['argv'], 1);
            return;
        }
        
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
            $uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        } elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        if (strncmp($uri, '?/', 2) === 0) {
            $uri = substr($uri, 2);
        }
        $parts = preg_split('#\?#i', $uri, 2);
        $uri = $parts[0];
        if (isset($parts[1])) {
            $_SERVER['QUERY_STRING'] = $parts[1];
            parse_str($_SERVER['QUERY_STRING'], $_GET);
        } else {
            $_SERVER['QUERY_STRING'] = '';
            $_GET = array();
        }

        if ($uri == '/' || empty($uri)) {
            $uri = '/';
        }else{
            $uri = parse_url($uri, PHP_URL_PATH);
            $uri = str_replace(array('//', '../'), '/', trim($uri, '/'));
        }
        
        $this->pathUri = explode('/', rtrim($uri, '/'));
        
        $pos = strpos($_SERVER['PHP_SELF'], INDEX_FILE);
        $this->baseUri = $this->isHttps() ? 'https://' : 'http://';
        $this->baseUri .= $_SERVER['HTTP_HOST'] . substr($_SERVER['PHP_SELF'], 0, $pos);
        $this->defaultController = self::$staticDefaultController;
    }

    /**
     * Does this site use https?
     *
     * @return boolean
     */
    public function isHttps() {
        if (isset($_SERVER['HTTPS'])) {

            if ('on' == strtolower($_SERVER['HTTPS']))
                return true;
            if ('1' == $_SERVER['HTTPS'])
                return true;
        }
        elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {

            return true;
        }

        return false;
    }

    /**
     * Clean the 'standard' model query.
     *
     * @param string
     * @return string
     */
    public function removeQuery($path) {
        $pathAr = explode('?', $path);
        if (count($pathAr) > 0)
            $path = $pathAr[0];

        return $path;
    }

    /**
     * Break the string given from extractUriString() into class, method and request.
     *
     * @param    integer
     * @return  string
     */
    public function path($segment = false) {
        if ($segment !== false)
            return ( isset($this->pathUri[$segment]) && $this->pathUri[$segment] != INDEX_FILE ) ? $this->pathUri[$segment] : false;
        else
            return $this->pathUri;
    }

    /**
     * Get class name from the url.
     *
     * @return  string
     */
    public function getClass() {
        if ($uriString = $this->path(0)) {

            if ($this->stripUriString($uriString))
                return $uriString;
            else
                return false;
        }
        else {

            return $this->defaultController;
        }
    }

    /**
     * Get method name from the url.
     *
     * @return  string
     */
    public function getMethod($default = 'index') {
        $uriString = $this->path(1);

        if (isset($uriString) && !empty($uriString)) {

            if ($this->stripUriString($uriString))
                return $uriString;
            else
                return '';
        }
        else {

            return $default;
        }
    }

    /**
     * Get "GET" request from the url.
     *
     * @param    int
     * @return  array
     */
    public function getRequests($segment = 2) {
        $uriString = $this->path($segment);

        if (isset($uriString)) {

            $requests = array_slice($this->path(), $segment);

            return $requests;
        } else {
            return false;
        }
    }

    /**
     * Cleaner for class and method name
     *
     * @param string
     * @return boolean
     */
    public function stripUriString($uri) {
        $uri = (!preg_match('/[^a-zA-Z0-9_.-]/', $uri) ) ? true : false;
        return $uri;
    }

    /**
     * Setter for default controller
     *
     * @param string $defaultController
     * @return void
     */
    public function setDefaultController($defaultController) {
        self::$staticDefaultController = $defaultController;
        $this->defaultController = $defaultController;
    }

    /**
     * Getter for default controller
     */
    public function getDefaultController() {
        return $this->defaultController;
    }

    /**
     * Getter for baseUri
     *
     * @return string
     */
    public function getBaseUri() {
        return $this->baseUri;
    }

}