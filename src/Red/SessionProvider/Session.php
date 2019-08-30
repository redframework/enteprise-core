<?php
/** Red Framework
 * Session Class
 *
 * This Class Will Configure And Initialize Session
 * Set And Get Sessions in Crypt/DeCrypt Mode
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\SessionProvider;


use Red\Encryption\Crypter;

/**
 * Class Session
 * @package App
 */
class Session
{


    public static function initSession()
    {
        if (session_id() == NULL) {
            require_once ROOT_PATH . 'config/session.php';
            session_start();
        }
    }

    /**
     * @param $first_dimension
     * @param $value
     * @param null $second_dimension
     * @param null $third_dimension
     */
    public static function set($value, $first_dimension, $second_dimension = NULL, $third_dimension = NULL)
    {
        if(is_null($second_dimension) && is_null($third_dimension)) {
            $_SESSION[$first_dimension] = Crypter::encrypt($value);
        }
        else if(!is_null($second_dimension) && is_null($third_dimension)){
            $_SESSION[$first_dimension][$second_dimension] = Crypter::encrypt($value);
        } else if(!is_null($second_dimension) && !is_null($third_dimension)) {
            $_SESSION[$first_dimension][$second_dimension][$third_dimension] = Crypter::encrypt($value);
        }
    }


    /**
     * @param $first_dimension
     * @param null $second_dimension
     * @param null $third_dimension
     * @return bool
     */
    public static function remove($first_dimension, $second_dimension = NULL, $third_dimension = NULL)
    {
        if(is_null($second_dimension) && is_null($third_dimension)){
            if (isset($_SESSION[$first_dimension])) {
                unset($_SESSION[$first_dimension]);
                return TRUE;
            }
        }
        else if(!is_null($second_dimension) && is_null($third_dimension)) {
            if (isset($_SESSION[$first_dimension][$second_dimension])) {
                unset($_SESSION[$first_dimension][$second_dimension]);
                return TRUE;
            }
        } else if(!is_null($second_dimension) && !is_null($third_dimension)){
            if (isset($_SESSION[$first_dimension][$second_dimension][$third_dimension])) {
                unset($_SESSION[$first_dimension][$second_dimension][$third_dimension]);
                return TRUE;
            }
        }

        return FALSE;
    }


    /**
     * @param $first_dimension
     * @param null $second_dimension
     * @param null $third_dimension
     * @return bool|string
     */
    public static function get($first_dimension, $second_dimension = NULL, $third_dimension = NULL)
    {

        if(is_null($second_dimension) && is_null($third_dimension)){
            if (isset($_SESSION[$first_dimension])) {
                return Crypter::decrypt($_SESSION[$first_dimension]);
            }
        }
        else if(!is_null($second_dimension) && is_null($third_dimension)){
            if (isset($_SESSION[$first_dimension][$second_dimension])) {
                return Crypter::decrypt($_SESSION[$first_dimension][$second_dimension]);
            }
        } else if (!is_null($second_dimension) && !is_null($third_dimension)){
            if (isset($_SESSION[$first_dimension][$second_dimension][$third_dimension])) {
                return Crypter::decrypt($_SESSION[$first_dimension][$second_dimension][$third_dimension]);
            }
        }


        return FALSE;
    }

    /**
     * @return string|null
     */
    public static function getAll()
    {
        if (isset($_SESSION)) {
            return Crypter::decrypt($_SESSION);
        }
        return FALSE;
    }


    public static function destroy()
    {
        session_destroy();
    }

}