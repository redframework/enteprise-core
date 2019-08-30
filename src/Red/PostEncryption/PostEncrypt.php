<?php

/** Red Framework
 * Post Encryption System
 *
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\PostEncryption;

/**
 * Class Crypter
 * @package Red\PostEncryption
 */
class PostEncrypt
{

    const SEPARATOR1 = ' /=/ ';
    const SEPARATOR2 = ' /~/ ';

    public static $openssl_config; // Filename of the openssl.cnf config file.
    public static $RSA_key_length;



    /**
     * Create new RSA public and private session keys
     *
     * @return array
     */
    public static function createKeys()
    {
        $config = array(
            "config" => realpath(self::$openssl_config),
            "private_key_bits" => self::$RSA_key_length,
            "private_key_type" => OPENSSL_KEYTYPE_RSA
        );

        $private_key = '';
        $key_details = false;

        $p_key = openssl_pkey_new($config);
        if ($p_key) {

            $p_key_created = openssl_pkey_export($p_key, $private_key, null, $config);

        }
        if ($p_key_created) {

            $key_details = openssl_pkey_get_details($p_key);

        }

        if (!$key_details) {

            die("RSA key creation failed");

        }

        return array(
            "rsaPrivateKey" => $private_key,
            "rsaPublicKeyHex" => bin2hex($key_details['rsa']['n'])
        );
    }


    public static function createSessionKeys()
    {
        $keyArray = self::createKeys();
        $_SESSION['RSA_Private_key'] = $keyArray['rsaPrivateKey'];
        $_SESSION['RSA_Public_key'] = $keyArray['rsaPublicKeyHex'];
    }

    protected static function getHash($salt, $key)
    {
        $hash1 = md5($key . $salt, true);
        $hash2 = md5($hash1 . $key . $salt, true);
        $hash3 = md5($hash2 . $key . $salt, true);
        $hash4 = $hash1 . $hash2 . $hash3;

        $hash['key'] = substr($hash4, 0, 32);
        $hash['iv'] = substr($hash4, 32, 16);

        return $hash;
    }


    public static function aes256cbcEncrypt($decrypted, $key)
    {
        $salt = openssl_random_pseudo_bytes(8);
        $hash = self::getHash($salt, $key);
        $encrypted = openssl_encrypt($decrypted, "aes-256-cbc", $hash['key'], true, $hash['i']);

        return base64_encode('Salted__' . $salt . $encrypted);
    }


    public static function aes256cbcDecrypt($encrypted, $key)
    {
        $encrypted = base64_decode($encrypted);
        $salt = substr($encrypted, 8, 8);
        $encrypted = substr($encrypted, 16);
        $hash = self::getHash($salt, $key);
        return openssl_decrypt($encrypted, "aes-256-cbc", $hash['key'], true, $hash['iv']);
    }


    public static function reset()
    {
        unset($_SESSION['aesKey']);
        self::createSessionKeys();
    }

    public static function decodeForm()
    {
        if (!isset($_POST['RedCryption'])) return false; // Nothing to decrypt

        // Get and decrypt RedCryption_key if present
        if (isset($_POST['RedCryption_key'])) {
            if (!isset($_SESSION['RSA_Private_key'])) {
                die("RSA key not found");
            }
            $rsa_private_key = openssl_pkey_get_private($_SESSION['RSA_Private_key']);
            $encrypted = pack('H*', $_POST['RedCryption_key']);
            $aes_key = '';
            if (!openssl_private_decrypt($encrypted, $aes_key, $rsa_private_key)) {
                return false;
            }
            $_SESSION['aes_key'] = $aes_key;
            unset($_POST['RedCryption_key']);
        }

        if (!isset($_SESSION['aes_key'])) {
            die("Decrypt: AES key not found");
        }

        $aes_key = $_SESSION['aes_key'];

        // Decrypt post
        $encrypted = $_POST['RedCryption'];
        $decrypted = self::aes256cbcDecrypt($encrypted, $aes_key);

        $pairs = explode(self::SEPARATOR2, $decrypted);
        foreach ($pairs as $pair) {
            list($key, $value) = explode(self::SEPARATOR1, $pair);
            $_POST[$key] = $value;
        }

        $form_id = $_POST['RedCryption_form'];

        unset($_POST['RedCryption'], $_POST['RedCryption_form']);

        return $form_id;
    }

    /**
     * Encrypt the $data key/values array of formId
     * @param $data
     * @param $form_id
     * @return bool|string
     */
    public static function encodeData($data, $form_id)
    {
        if (!$data || !$form_id) {
            return false;
        }

        if (!isset($_SESSION['aesKey'])) {
            die("Encrypt: AES key not found");
        }

        // Serialize data and form id
        $union = '';
        foreach ($data as $key => $value) {
            $union .= $key . self::SEPARATOR1 . $value . self::SEPARATOR2;
        }
        $union .= 'RedCryption_form' . self::SEPARATOR1 . $form_id;

        // Encrypt the serialized data
        $encrypted = self::aes256cbcEncrypt($union, $_SESSION['aesKey']);

        // Return array of encripted key and data with formId
        return $encrypted;
    }

}