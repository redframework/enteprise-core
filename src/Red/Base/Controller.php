<?php
/** Red Framework
 * Controller Class
 * Create a Model Instance Automatically
 * Easy Access Render Function
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\Base;

use Red\View\View;
/**
 * Class Controller
 * @package app
 */
class Controller
{
    /**
     * @var Model $model
     */
    protected $model;

    public function __construct()
    {

        $controller_name = get_class($this);

        $controller_name = str_replace('Controller', '', $controller_name);

        $controller_name = substr($controller_name, strrpos($controller_name, '\\') + 1);

        if(file_exists(ROOT_PATH . 'app' . DS . 'models' . DS . $controller_name . 'model' . '.php')){

            $model = 'App' . DS. 'Models' . DS . $controller_name . 'Model';

            $this->model = new $model();

        }

    }

    /**
     * @param $viewPath
     * @param array $data
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function render($viewPath, $data = array())
    {
        view::render($viewPath, $data);
    }

    public static function generateRandomString($length = 10)
    {
        return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }


    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function notFound()
    {
        $this->render('@Router/NotFoundError');
        http_response_code(404);
    }

}