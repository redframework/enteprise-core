<?php
/** Red Framework
 * Router Class For Red Framework
 *
 * @author RedCoder
 * @version 1.0
 * @copyright 2019
 * http://redframework.ir
 */

namespace Red\RouterService;

use Red\Base\Middleware;
use Red\Red;
use Red\SanitizeService\Sanitize;
use Red\EnvironmentProvider\Environment;
use Red\View\View;

class Router
{
    /**
     * @var array
     */
    private static $routes = array();
    private static $url;
    private static $url_count;
    public static $default_route;
    private static $default_no_access;


    /**
     * @param $route_path
     * @param $route_method
     * @param $route_action
     * @param array $middlewares
     */
    public static function register($route_path, $route_method, $route_action, $middlewares = [])
    {

        //Check If Any Parameter Inserted In Path
        if (preg_match_all('/{[?a-zA-Z]+}/', $route_path)) {

            preg_match_all('/{[?a-zA-Z]+}/', $route_path, $matches);

            foreach ($matches[0] as $param) {
                $param = trim($param, '{}');

                if (strpos($param, '?') === 0 || strrpos($param, '?') === 0) {
                    $parameters[] = '?';
                } else {
                    $parameters[] = trim($param, '{}');
                }
            }
        }


        //Slice Path
        preg_match_all('/.*([\/]*)(.*)/', $route_path, $matches);

        //preg_match_all Will Give Dimensional Array so We Implode Parts That We Need To String Again
        $route_path = implode($matches[0], '/');
        $route_path = rtrim($route_path, '/');

        $route_path = preg_replace('/{[?a-zA-Z]+}/', '', $route_path);


        if (mb_strpos($route_path, '/') !== mb_strlen($route_path) - 1) {
            $route_path .= '/';
        }

        $route_path = preg_replace('/(\/){2}/', '/', $route_path);

        $route_path = mb_strtolower($route_path);


        $route_method = strtolower($route_method);
        $route_method = Sanitize::sanitize($route_method, "space");
        $route_method = explode(',', $route_method);


        //Check If Any Parameter Is Inserted
        if (isset($parameters)) {
            self::$routes[$route_path] = array(
                'action' => $route_action,
                'parameters' => $parameters,
                'method' => $route_method,
                'middlewares' => $middlewares
            );
        } else {
            self::$routes[$route_path] = array(
                'action' => $route_action,
                'method' => $route_method,
                'middlewares' => $middlewares
            );
        }
    }

    /**
     * Route
     *
     * @param $url
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */

    public static function route($url)
    {

        // Saving Route Analytics as JSON for Red Analytics Java App If Production Mode is On

        if (strtolower(Environment::get("PROJECT", "State")) == "production") {
            $route_analytics = json_encode(self::$routes, TRUE);

            $file_handler = fopen(ROOT_PATH . 'storage' . DS . 'Analytics' . DS . 'Routes.json', "w");
            fwrite($file_handler, $route_analytics);
            fclose($file_handler);
        }

        //URL

        self::$url = $url;


        if (self::$url == "") {
            self::$url = "/";
        } else if (self::$url != '/' && self::$url != '' && mb_strrpos(self::$url, '/') + 1 !== mb_strlen(self::$url)) {
            self::$url .= '/';
        }

        self::$url_count = count(explode('/', trim(self::$url, '/')));


        //Check If Any Route Registered
        if (count(self::$routes) > 2) {

            $counter = 1;

            //$routes Is An 2D Array Contains path as Key , config And params As Value
            foreach (self::$routes as $path => $config) {

                if ($path != '/' && $path != '') {
                    $path_count = count(explode('/', trim($path, '/')));
                } else {
                    $path_count = 0;
                }


                $middlewares = $config['middlewares'];

                $url_match = mb_substr(self::$url, 0, mb_strlen($path));
                $url_parameters = mb_substr(self::$url, mb_strlen($path));
                $url_parameters = trim($url_parameters, '/');
                $url_parameters = explode('/', $url_parameters);

                $url_without_parameters = mb_substr(self::$url, 0, mb_strlen($path));

                if (isset($config['parameters'])) {
                    $parameters_count = count($config['parameters']);
                    $filtered_parameters = self::removeOptionalParams($config['parameters']);
                    $filtered_parameters_count = count($filtered_parameters);
                } else {
                    $filtered_parameters_count = 0;
                    $parameters_count = 0;
                }


                //Check If Action Is Closure Object or Not
                if (is_callable($config['action']) && !is_string($config['action']) && $url_match === $path) {


                    //Counting Different Of Url And Path, The Different Will Be Parameters
                    if ($url_without_parameters == $path) {

                        if (self::$url_count <= $path_count + $parameters_count && self::$url_count >= $path_count + $filtered_parameters_count) {
                            //If Any Parameter Was Inserted Call Closure Object With Parameters
                            if (isset($url_parameters)) {
                                if (!in_array(strtolower($_SERVER['REQUEST_METHOD']), $config['method'])) {
                                    http_response_code(403);
                                    self::defaultNoAccess();
                                    return FALSE;
                                }

                                if (count($middlewares) > 0) {

                                    array_push(Middleware::$middlewares, $middlewares);

                                    foreach ($middlewares as $middleware) {
                                        $middleware = explode(":", $middleware);
                                        if (count($middleware) == 2) {
                                            Middleware::initialize($middleware[0], $middleware[1]);
                                        } else {
                                            Middleware::initialize($middleware[0]);
                                        }
                                    }
                                }


                                $function = $config['action'];
                                call_user_func_array($function, $url_parameters);
                                return TRUE;
                            } else if (!isset($url_parameters)) {
                                if (count($middlewares) > 0) {

                                    array_push(Middleware::$middlewares, $middlewares);

                                    foreach ($middlewares as $middleware) {
                                        $middleware = explode(":", $middleware);
                                        if (count($middleware) == 2) {
                                            Middleware::initialize($middleware[0], $middleware[1]);
                                        } else {
                                            Middleware::initialize($middleware[0]);
                                        }
                                    }
                                }

                                $function = $config['action'];
                                call_user_func($function);
                                return TRUE;
                            }
                        } else {
                            self::defaultRoute();
                            return FALSE;
                        }
                    }
                } //Get Controller And Method From routeAction
                else if (!is_callable($config['action']) && is_string($config['action'])) {

                    $controller_method = explode("@", $config['action']);
                    $controller = $controller_method[0];
                    if (isset($controller_method[1])) {
                        $method = $controller_method[1];
                    } else {
                            $error_no = "Bad Route";
                            $error_message = "Route Named '" . $path . "' Causes Problem";
                            Red::generateError($error_no, $error_message);
                    }


                    if (strtolower($controller) == "apiservice") {
                        $namespace = 'App' . DS . 'APIs' . DS . $controller;
                    } else {
                        $namespace = 'App' . DS . 'Controllers' . DS . $controller;
                    }


                    if (!isset($method) || !method_exists($namespace, $method) && !is_callable($config['action'])) {

                        if ($url_match == $path && $url_without_parameters == $path) {
                            if (Environment::get("DEBUG", "Errors") == 'on') {
                                $error_no = 'You Defined a Route Action which not Exist';
                                $error_message = 'Method Does not Exist';
                                http_response_code(500);
                                View::render('@Router/Error', compact('error_no', 'error_message'));
                                return FALSE;
                            } else {
                                http_response_code(500);
                                View::render('@Router/UserError');
                                return FALSE;
                            }
                        }
                    }
                }


                //Execute Method Without Parameter
                if (isset($method) && method_exists($namespace, $method) && isset($filtered_parameters_count) && !isset($function) && self::$url == $path) {
                    if ($filtered_parameters_count == 0) {
                        if (isset($url_parameters)) {
                            if (!in_array(strtolower($_SERVER['REQUEST_METHOD']), $config['method'])) {
                                http_response_code(403);
                                self::DefaultNoAccess();
                                return FALSE;
                            }
                        }


                        if (count($middlewares) > 0) {

                            array_push(Middleware::$middlewares, $middlewares);

                            foreach ($middlewares as $middleware) {
                                $middleware = explode(":", $middleware);
                                if (count($middleware) == 2) {
                                    Middleware::initialize($middleware[0], $middleware[1]);
                                } else {
                                    Middleware::initialize($middleware[0]);
                                }
                            }
                        }


                        $instance = new $namespace();
                        $instance->$method();
                        return TRUE;
                    }
                } //Execute Method With Parameter
                else if (isset($method) && method_exists($namespace, $method) && isset($config['parameters']) && !is_callable($config['action']) && $url_match == $path) {
                    if (isset($url_parameters)) {
                        if (!in_array(strtolower($_SERVER['REQUEST_METHOD']), $config['method'])) {
                            http_response_code(403);
                            self::DefaultNoAccess();
                            return FALSE;
                        }
                    }


                    if (self::$url_count <= $path_count + $parameters_count && self::$url_count >= $path_count + $filtered_parameters_count) {


                        if (count($middlewares) > 0) {

                            array_push(Middleware::$middlewares, $middlewares);

                            foreach ($middlewares as $middleware) {
                                $middleware = explode(":", $middleware);
                                if (count($middleware) == 2) {
                                    Middleware::initialize($middleware[0], $middleware[1]);
                                } else {
                                    Middleware::initialize($middleware[0]);
                                }
                            }
                        }


                        $instance = new $namespace();
                        call_user_func_array(array(
                            $instance,
                            $method
                        ), $url_parameters);
                        return TRUE;
                    }
                }


                //Check If Any Route Registered For Url Or Not
                if ($counter == count(self::$routes)) {
                    self::defaultRoute();
                    return FALSE;
                } else {

                    $counter++;
                    continue;
                }
            }


        } else {
            view::render("@Router/Home");
        }
        return FALSE;
    }


    private static function removeOptionalParams($params)
    {
        foreach ($params as $key => $value) {
            if ($value !== '?') {
                $filtered_params[$key] = $value;
            }
        }

        if (isset($filtered_params)) {
            return $filtered_params;

        }

        return array();
    }


    /**
     * @return bool
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    private static function defaultRoute()
    {
        if (is_null(self::$default_route)) {
            http_response_code('404');
            View::render('@Router/NotFoundError');
            return TRUE;
        } else {
            $CM = self::$default_route;
        }


        //Check If Action Is Closure Object or Not
        if (is_callable(self::$default_route) && !is_string(self::$default_route)) {
            $function = self::$default_route;
            call_user_func($function);
            return TRUE;
        } else if (!is_callable(self::$default_route) && is_string(self::$default_route)) {

            $CM = explode('@', $CM);
            $controller = 'App' . DS . 'controllers' . DS . $CM[0];
            $method = $CM[1];

            if (isset($method) && method_exists($controller, $method) && !isset($parameters)) {
                $instance = new $controller();
                $instance->$method();
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * @param $CM
     */
    public static function setDefaultRoute($CM)
    {
        self::$default_route = $CM;
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public static function defaultNoAccess()
    {

        if (strlen(self::$default_no_access) == 0) {
            http_response_code('403');
            View::render('@Router/AccessError');
        } else {
            http_response_code('403');
            View::render(self::$default_no_access);
        }

    }

    public static function setDefaultNoAccess($view)
    {
        self::$default_no_access = $view;
    }


}

