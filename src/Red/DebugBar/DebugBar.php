<?php
/** Red Framework
 * DebugBar Component
 *
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\DebugBar;


use App\Bootstrap\Bootstrap;
use Red\Base\Middleware;
use Red\Base\Model;
use Red\CookieProvider\Cookie;
use Red\EnvironmentProvider\Environment;
use Red\InputProvider\Input;
use Red\SessionProvider\Session;
use Red\View\View;

class DebugBar
{

    private $resource_base;
    private static $messages = array();

    public function __construct($resource_base = "public/Red/DebugBar/")
    {
        $this->resource_base = $resource_base;
    }

    public function render(){
        $resource_base = $this->resource_base;
        $php_version = phpversion();
        $ping = Bootstrap::getExecutionTime();
        $memory_usage = memory_get_usage(true);
        $usage_unit = array('B','KB','MB','GB','TB','PB');
        $memory_usage = round($memory_usage/pow(1024,($i=floor(log($memory_usage,1024)))),2).' '.$usage_unit[$i];
        $http_status_code = http_response_code();
        $messages = self::$messages;
        $project_variables = Environment::get("PROJECT");
        $database_config = [
            "database_1" => Environment::get("DATABASE_1"),
            "database_2" => Environment::get("DATABASE_2"),
            "database_3" => Environment::get("DATABASE_3"),
            ];

        $db1_queries = Model::getQueryHistory(DB1);
        $db2_queries = Model::getQueryHistory(DB2);
        $db3_queries = Model::getQueryHistory(DB3);

        ob_start();
        var_dump(Session::getAll());
        $session_variables = ob_get_contents();
        ob_end_clean();

        ob_start();
        var_dump(Input::getAll());
        $get_variables = ob_get_contents();
        ob_end_clean();

        ob_start();
        var_dump(Input::postAll());
        $post_variables = ob_get_contents();
        ob_end_clean();

        ob_start();
        var_dump(Cookie::getAll());
        $cookie_variables = ob_get_contents();
        ob_end_clean();

        if (isset(Middleware::$middlewares[0])){
            $middlewares = Middleware::$middlewares[0];
        } else {
            $middlewares = ['No Middleware Been Used'];
        }

        View::render("@DebugBar/DebugBar", compact("resource_base", "php_version", "ping", "memory_usage",
            "http_status_code", "messages", "project_variables", "database_config", "db1_queries", "db2_queries", "db3_queries",
            "session_variables", "get_variables",
            "post_variables", "cookie_variables", "middlewares"));
    }

    /**
     * @return mixed
     */
    public static function collectMessages()
    {
        return self::$messages;
    }

    /**
     * @param mixed $message
     */
    public static function addMessage($message)
    {
        array_push(self::$messages, $message);
    }



}