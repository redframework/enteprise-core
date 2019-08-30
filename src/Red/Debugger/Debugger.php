<?php
/** Red Framework
 * Debugger Component
 *
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\Debugger;


use Red\View\View;

class Debugger
{
    private static $status;


    public static function enable()
    {
        self::$status = TRUE;
        ini_set('display_errors', 'off');
        error_reporting(E_ALL);
        set_error_handler(array("App\Red\Debugger\Debugger", "errorHandler"), E_ALL);

        register_shutdown_function(array("App\Red\Debugger\Debugger", "fatalHandler"));
    }


    public static function disable()
    {
        self::$status = FALSE;
        ini_set('display_errors', 'off');
        error_reporting(E_ALL);
        set_error_handler(array("App\Red\Debugger\Debugger", "userErrorHandler"), E_ALL);
    }

    public static function errorHandler($error_no, $error_string, $error_file, $error_line)
    {

        http_response_code(500);
        $stack_trace = debug_backtrace();
        $stack_trace = array_slice($stack_trace, 1);
        $code_file_handler = fopen($error_file, "r");
        $code = fread($code_file_handler, filesize($error_file));
        fclose($code_file_handler);
        $code = explode("\n", $code);
        View::render("@Debugger/Debugger", compact("stack_trace", "code", "error_string", "error_line"));
        exit();
    }

    public static function fatalHandler()
    {

        $parameters = error_get_last();
        if ($parameters != null) {
            self::errorHandler($parameters['type'], $parameters['message'], $parameters['file'], $parameters['line']);
        }

    }

    public static function userErrorHandler($error_no, $error_string, $error_file, $error_line){
        View::render("@ErrorHandler/UserError");
        exit();
    }

}