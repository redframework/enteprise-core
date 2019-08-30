<?php
/** Red Framework
 * Middleware Base Class
 * @author REDCODER
 * http://redframework.ir
 */

namespace Red\Base;


abstract class Middleware
{
    public static $middlewares = array();

    public function __construct($parameters = NULL)
    {
        $middleware_status = $this->run($parameters);

        if ($middleware_status == FALSE) {
            exit(0);
        }
    }

    public static function initialize($middleware_name, $parameters = null)
    {

        $middleware = 'App' . DS . 'Middlewares' . DS . $middleware_name;

        if ($parameters != null) {
            new $middleware($parameters);
        } else {
            new $middleware();
        }

    }

    public abstract function run(... $parameters);

}