<?php
/**
 * Hendle every http errors.
 *
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 * @link	http://panadaframework.com/
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @since	version 1.0.0
 * @package	Resources
 */
namespace Resources;

class HttpException extends \Exception
{
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        set_exception_handler( array($this, 'main') );
        parent::__construct($message, $code, $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function main($exception)
    {
        self::outputError($exception);
    }

    public static function outputError($exception)
    {

        if (PHP_SAPI == 'cli') {
            echo $exception->getMessage()
                ."\nFile: ".$exception->getFile()
                    ." on line ".$exception->getLine()
                ."\n\n".$exception->getTraceAsString()
                ."\n";
            // exit with an error code
            exit(1);
        }

        // Write the error to log file
        @error_log('Error 404 Page Not Found: '.$_SERVER['REQUEST_URI']);

        header('HTTP/1.1 404 Not Found', true, 500);
        \Resources\Controller::outputError(
              'errors/404'
            , array('message' => $exception->getMessage())
            );
    }
}
