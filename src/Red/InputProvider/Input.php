<?php
/** Red Framework
 * Controlling Input Class
 * Involves Get, Post, File GLOBAL Variables
 *
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\InputProvider;

class Input
{
    private static $get;
    private static $post = array();
    private static $files;

    public static function initInput(){
        if (isset($_GET)){
            self::$get = $_GET;
            unset($_GET);
        }

        if (isset($_POST)){
            self::$post = $_POST;
            unset($_POST);
        }


        $post_query_string = file_get_contents('php://input');

        $data = array();

        parse_str($post_query_string, $data);

        self::$post = array_merge(self::$post, $data);



        if (isset($_FILES)){
            self::$files = $_FILES;
            unset($_FILES);
        }
    }

    public static function getAll(){
        if(isset(self::$get)){
            return self::$get;
        } else {
            return FALSE;
        }
    }

    public static function get($variable){
        if(isset(self::$get[$variable])){
            return self::$get[$variable];
        } else {
            return FALSE;
        }
    }

    public static function getUnset($variable){
        if(isset(self::$get[$variable])){
            unset(self::$get[$variable]);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function postAll(){
        if (isset(self::$post)){
            return self::$post;
        } else {
            return FALSE;
        }
    }


    public static function post($variable){
        if (isset(self::$post[$variable])){
            return self::$post[$variable];
        } else {
            return FALSE;
        }
    }


    public static function postUnset($variable){
        if(isset(self::$post[$variable])){
            unset(self::$post[$variable]);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function file($file){
        if(isset(self::$files[$file])){
            return self::$files[$file];
        } else {
            return FALSE;
        }
    }

    public static function fileUnset($file){
        if(isset(self::$files[$file])){
            unset(self::$files[$file]);
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
