<?php

/**
 * Hendle every runtime code execution errors.
 *
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @link	http://panadaframework.com/
 *
 * @license	http://www.opensource.org/licenses/bsd-license.php
 *
 * @since	version 1.0.0
 */
namespace Resources;

class RunException extends \ErrorException
{
    public function __construct($message = null, $code = 0, $severity = 1, $filename = __FILE__, $lineno = __LINE__, Exception $previous = null)
    {
        parent::__construct($message, $code, $severity, $filename, $lineno, $previous);
    }

    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }

    public static function main($exception)
    {
        $message = $exception->getMessage();
        $file = false;
        $line = false;
        $traceAsString = $exception->getTraceAsString();

        foreach ($exception->getTrace() as $trace) {
            if (isset($trace['file'])) {
                $file = $trace['file'];
                if (isset($trace['line'])) {
                    $line = $trace['line'];
                }
                break;
            }
        }

        return self::outputError($message, $file, $line, $traceAsString);
    }

    public static function errorHandlerCallback($errno, $message, $file, $line)
    {
        throw new self($message, 0, 1, $file, $line);
    }

    public static function outputError($message = null, $file = false, $line = false, $trace = false)
    {
        // Message for log
        $errorMessage = 'Error '.$message.' in '.$file.' line: '.$line;

        // Write the error to log file
        @error_log($errorMessage);

        // Just output the error if the error source for view file or if in cli mode.
        if (PHP_SAPI == 'cli') {
            exit($errorMessage);
        }

        $code = [];

        if (!$file) {
            goto constructViewData;
        }

        $fileString     = file_get_contents($file);
        $arrLine        = explode("\n", $fileString);
        $totalLine      = count($arrLine);
        $getLine        = array_combine(range(1, $totalLine), array_values($arrLine));
        $startIterate   = $line - 5;
        $endIterate     = $line + 5;

        if ($startIterate < 1) {
            $startIterate  = 1;
        }

        if ($endIterate > $totalLine) {
            $endIterate = $totalLine;
        }

        for ($i = $startIterate; $i <= $endIterate; $i++) {
            $html = '<span style="margin-right:10px;background:#CFCFCF;">'.$i.'</span>';

            if ($line == $i) {
                $html .= '<span style="color:#DD0000">'.htmlentities($getLine[$i])."</span>\n";
            } else {
                $html .= htmlentities($getLine[$i])."\n";
            }

            $code[] = $html;
        }

        constructViewData:

        $data = [
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'code' => $code,
            'trace' => $trace,
        ];

        $response = new Response;
        $response->setStatusCode(500);
        $response->setBody(Controller::outputError('errors/500', $data));
        
        return $response;
    }

    /**
     * EN: generates class/method backtrace.
     *
     * @param int
     *
     * @return array
     */
    public static function getErrorCaller($offset = 1)
    {
        $caller = array();
        $bt = debug_backtrace(false);
        $bt = array_slice($bt, $offset);
        $bt = array_reverse($bt);

        foreach ((array) $bt as $call) {
            if (!isset($call['class'])) {
                continue;
            }

            if (@$call['class'] == __CLASS__) {
                continue;
            }

            $function = $call['class'].'->'.$call['function'];

            if (isset($call['line'])) {
                $function .= ' line '.$call['line'];
            }

            $caller[] = $function;
        }

        $caller = implode(', ', $caller);

        return $caller;
    }
}
