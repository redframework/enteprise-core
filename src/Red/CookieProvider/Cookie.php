<?php
/** Red Framework
 * Cookie Class
 *
 * This Class Will Set And Get Cookies
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\CookieProvider;

/**
 * Class Cookie
 * @package App
 */
class Cookie
{
    /**
     * @param $cookie
     * @param $value
     * @param $expire_days
     * @param null $path
     */
    public static function set($cookie, $value, $expire_days, $path = null)
    {

        $expire_days *= 24 * 60 * 60;

        $expire_days += time();


        if ($path == null) {
            $path = '/';
        }

        setCookie($cookie, $value, $expire_days, $path);
    }

    /**
     * @param $cookie
     */
    public static function remove($cookie)
    {
        if (isset($_COOKIE[$cookie])) {
            self::set($cookie, '', time() - 1000);
            unset($_COOKIE[$cookie]);
        }

    }

    /**
     * @param $cookie
     * @return bool|string
     */
    public static function get($cookie)
    {
        if (isset($_COOKIE[$cookie])) {
            return $_COOKIE[$cookie];
        } else {
            return FALSE;
        }
    }

    /**
     * @return bool|string
     */
    public static function getAll()
    {
        if (isset($_COOKIE)) {
            return $_COOKIE;
        } else {
            return FALSE;
        }
    }

}