<?php
/** RedFramework
 * Captcha Generation
 * Captcha Verification
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\CaptchaService;


use Red\Base\Controller;
use Red\EnvironmentProvider\Environment;
use Red\InputProvider\Input;
use Red\SessionProvider\Session;

class Captcha
{

    public static function generate()
    {

        $image_files = scandir(CAPTCHA_PATH . 'images');
        if (count($image_files) > 200) {
            foreach ($image_files as $file) {
                if ($file !== '.' && $file !== '..' && $file !== '...') {
                    if (file_exists(CAPTCHA_PATH . 'Images/' . $file)) {
                        chmod(CAPTCHA_PATH . 'Images/' . $file, 0777);
                        unlink(CAPTCHA_PATH . 'Images/' . $file);
                    }
                }
            }
        }

        $image = imagecreatetruecolor(200, 50);
        $background_color = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 200, 50, $background_color);

        $line_color = imagecolorallocate($image, 64, 64, 64);
        for ($i = 0; $i < 10; $i++) {
            imageline($image, 0, rand() % 50, 200, rand() % 50, $line_color);
        }

        $pixel_color = imagecolorallocate($image, 0, 0, 255);
        for ($i = 0; $i < 1000; $i++) {
            imagesetpixel($image, rand() % 200, rand() % 50, $pixel_color);
        }

        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $len = strlen($letters);

        $text_color = imagecolorallocate($image, 0, 0, 0);

        $word = '';

        for ($i = 0; $i < 6; $i++) {
            $letter = $letters[rand(0, $len - 1)];
            imagettftext($image, 25, 0, 15 + ($i * 30), 38, $text_color, ROOT_PATH . 'public' . DS . 'Red' . DS . 'Fonts' . DS . 'tahoma.ttf', $letter);
            $word .= $letter;
        }

        $rand = Controller::generateRandomString(10);

        imagepng($image, CAPTCHA_PATH . "Images/captcha_" . $rand . ".png");

        Session::set(strtolower($word), 'captcha_code');
        Session::set(PUBLIC_PATH . 'Red/Captcha/Images/captcha_' . $rand . '.png', 'captcha_new_path');

        if (Environment::get('PROJECT', 'Language') == 'fa') {
            return '<div style="border-radius: 5px;text-shadow: 0 0 4px;"><img id="captcha-image" draggable="false" src="' . PUBLIC_PATH . 'Red/Captcha/Images/captcha_' . $rand . '.png" alt="تصویر امنیتی">
                <br/>
                <a id="captcha-regenerate" style="cursor: pointer;position:relative;text-align:center;text-decoration: none;">
                ایجاد کد جدید
                </a>
                 <script>
                
                Red.ready(function () {
                Red.select("#captcha-regenerate").on("click", function () {

                     Red.ajax({
                        type: "GET",
                        url: "new-captcha",
                        contentType: "application/json",
                        success: function () {
                                                      
                            var captcha_new_path = Red.parseJSON(this.response);

                            Red.select("#captcha-image").source(captcha_new_path["path"]);
                            
                            

                        }
                    });

                });
            });
                
               
                
                </script>
                </div>';
        } else {
            return '<div style="border-radius: 5px;text-shadow: 0 0 4px;"><img id="captcha-image" draggable="false" src="' . PUBLIC_PATH . 'Red/Captcha/Images/captcha_' . $rand . '.png" alt="Captcha Image">
                <br/>
                <a id="captcha-regenerate" style="cursor: pointer;position:relative;text-align:center;text-decoration: none;">
                Regenerate
                </a>
                 <script>
                
                Red.ready(function () {
                Red.select("#captcha-regenerate").on("click", function () {

                     Red.ajax({
                        type: "GET",
                        url: "new-captcha",
                        contentType: "application/json",
                        success: function () {
                                                      
                            var captcha_new_path = Red.parseJSON(this.response);

                            Red.select("#captcha-image").source(captcha_new_path["path"]);
                            
                            

                        }
                    });

                });
            });
                
               
                
                </script>
                </div>';
        }
    }

    public static function verify()
    {
        if (is_string(Input::post('captcha_code'))) {
            if (strtolower(Input::post('captcha_code')) == Session::get('captcha_code')) {
                return TRUE;
            } else {
                return FALSE;
            }
        }

        return FALSE;
    }


    public static function CaptchaRegenerateAPI()
    {

            self::generate();
            Environment::set('DEBUG',  'DebugBar', 'off');
            $captcha_new_path = ['path' => Session::get('captcha_new_path')];
            return json_encode($captcha_new_path);

    }
}