<?php
/** Red Framework
 * Red Base Class
 * @author REDCODER
 * http://redframework.ir
 */

namespace Red;


use Red\EnvironmentProvider\Environment;
use Red\InputProvider\Input;
use Red\FilterService\Filter;
use Red\MailService\Mail;
use Red\SanitizeService\Sanitize;
use Red\ValidateService\Validate;
use Red\View\View;

class Red
{

    private static $php_config = FALSE;
    private static $errors = array();

    public static function getErrors(){
        return self::$errors;
    }

    public static function pushError($error){
        array_push(self::$errors, $error);
        return TRUE;
    }

    public static function generateError($error_no, $error_message = null){
        http_response_code(500);

        if (Environment::get('DEBUG', 'Errors') == 'on') {
            view::render('@ErrorHandler/Error', compact('error_no', 'error_message'));
        } else {
            View::render('@ErrorHandler/UserError');
        }

        exit();
    }

    public static function get($variable){
        Input::get($variable);
    }

    public static function post($variable){
        Input::post($variable);
    }

    public static function file($file){
        Input::file($file);
    }

    public static function filter($string, $method){
        Filter::filter($string, $method);
    }

    public static function sanitize($string, $method){
        Sanitize::sanitize($string, $method);
    }

    public static function validate($string, $attribute){
        Validate::validate($string, $attribute);
    }

    public static function sendMail($subject, $target, $body, $flag = NULL){
        Mail::send($subject, $target, $body, $flag);
    }


    public static function getPhpConfig()
    {
        return self::$php_config;
    }

    public static function setPhpConfig($php_config)
    {
        self::$php_config = $php_config;
    }



}