<?php
/** Red Framework
 * Language Class
 * Get Language Errors/Events and Etc From Resources Directory
 *
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\LanguageService;

use Red\CookieProvider\Cookie;
use Red\EnvironmentProvider\Environment;

class Language
{
    public static function get($no, $field)
    {


        if (Cookie::get('language') == TRUE) {

            $language = Cookie::get('language');
            $file_path = LANGUAGE_PATH . $language . DS . $language . ".json";

            if (file_exists($file_path)) {
                $language_parameters = json_decode(file_get_contents($file_path), TRUE);
                return $language_parameters[$language][$no][$field];
            } else {

                $language = Environment::get("PROJECT", "Langauge");
                $file_path = LANGUAGE_PATH . $language . DS . $language . ".json";

                if (file_exists($file_path)) {
                    $language_parameters = json_decode(file_get_contents($file_path), TRUE);
                    return $language_parameters[$language][$no][$field];
                } else {
                    return FALSE;
                }

            }

        } else {
            $language = Environment::get("PROJECT", "Langauge");
            $file_path = LANGUAGE_PATH . $language . DS . $language . ".json";

            if (file_exists($file_path)) {
                $language_parameters = json_decode(file_get_contents($file_path), TRUE);
                return $language_parameters[$language][$no][$field];
            } else {
                return FALSE;
            }
        }
    }

    public static function getCurrentLanguage(){
        return Cookie::get('language');
    }

    public static function set($language){
        Cookie::set('language', $language, time() + (60 * 60 * 24 *7));
    }

}