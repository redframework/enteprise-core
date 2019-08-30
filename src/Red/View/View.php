<?php
/** Red Framework
 * View Class
 *
 * - Setting Up Template Engine
 * - Render View
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\View;

use App\Bootstrap\Bootstrap;
use Red\CaptchaService\Captcha;
use Red\EnvironmentProvider\Environment;
use Red\InputProvider\Input;
use Red\SessionProvider\Session;
use App\Middlewares\CSRFToken;
use Twig\TwigFunction;
use Twig\Environment as twig_environment;

class View
{
    /**
     * @param $view_path
     * @param array $data
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */


    public static function render($view_path, $data = array())
    {

        $loader = new \Twig\Loader\FilesystemLoader(VIEW_PATH);

        $loader->addPath(ROOT_PATH . 'vendor' . DS . 'redframework' . DS . 'enterprise-core' . DS . 'src' . DS . 'Red' . DS . 'ErrorHandler', 'ErrorHandler');
        $loader->addPath(ROOT_PATH . 'vendor' . DS . 'redframework' . DS . 'enterprise-core' . DS . 'src' . DS . 'Red' . DS . 'RouterService' . DS . 'Views', 'Router');
        $loader->addPath(ROOT_PATH . 'vendor' . DS . 'redframework' . DS . 'enterprise-core' . DS . 'src' . DS . 'Red' . DS . 'Debugger' . DS . 'Views', 'Debugger');
        $loader->addPath(ROOT_PATH . 'vendor' . DS . 'redframework' . DS . 'enterprise-core' . DS . 'src' . DS . 'Red' . DS . 'DebugBar' . DS . 'Views', 'DebugBar');


        if (Environment::get('PROJECT', 'Cache') == 'on') {
            $template_engine = new twig_environment($loader, [
                'cache' => TEMPLATE_ENGINE_CACHE_PATH,
            ]);
        } else {
            $template_engine = new twig_environment($loader);
        }


        $template_engine->addFunction(new TwigFunction('rootPath', function () {
            if (ROOT_DIRECTORY != '/') {
                $root_path = trim(ROOT_DIRECTORY, '/\\');
                $root_path = '/' . $root_path . '/';
                return $root_path;
            } else {
                return '/';
            }

        }));


        $template_engine->addFunction(new TwigFunction('self', function () {
            return Input::get("url");
        }));

        $template_engine->addFunction(new TwigFunction('CSRFToken', function () {
            return CSRFToken::generate();
        }));

        $template_engine->addFunction(new TwigFunction('RedJS', function () {
            if (ROOT_DIRECTORY != '/') {
                $root_path = trim(ROOT_DIRECTORY, '/\\');
                $root_path = '/' . $root_path . '/';
                return '<script src="' . $root_path . 'public/red/RedJS/Red.min.js' . '"></script>';
            } else {
                return '<script src="/public/Red/RedJS/Red.min.js"></script>';
            }

        }));

        $template_engine->addFunction(new TwigFunction('captchaCode', function () {
            return Captcha::generate();
        }));

        $template_engine->addFunction(new TwigFunction('config', function () {
            $config = Environment::get();
            echo "<div id='information-button' style='margin: 20px auto 10px auto;text-align: center;width: 90%;background-color: transparent;border: 1.2px rgb(226,90,90) solid;border-radius: 3px;padding: 5px;cursor: pointer;color: rgb(226,90,90);
            height: 18px;opacity: 0.68;'>";
            echo "Information";
            echo "</div>";
            echo "<div id='information-container'>";
            echo "Your IP : " . $_SERVER['REMOTE_ADDR'] . "<br/>";
            echo "Server IP : " . $_SERVER['SERVER_ADDR'] . "<br/>";

            echo "Execution Time : " . Bootstrap::getExecutionTime() . " MS <br/> (Till Line " . __LINE__ . " ) <br/>";

            echo "PHP Version : " . PHP_VERSION;
            echo "</div>";

            echo "<div id='project-button' style='margin: 18px auto 18px auto;text-align: center;width: 90%;background-color: transparent;border: 1.2px rgb(226,90,90) solid;border-radius: 3px;padding: 5px;cursor: pointer;color: rgb(226,90,90);
                        height: 18px;opacity: 0.68;'>";
            echo "Project";
            echo "</div>";

            echo "<div id='project-container' style='display: none'>";
            echo "Project : <br>";
            foreach ($config['PROJECT'] as $key => $value) {
                echo $key . " : " . $value;
                echo '<br/>';
            }
            echo "</div>";

            echo "<div id='database1-button' style='margin: 18px auto 18px auto;text-align: center;width: 90%;background-color: transparent;border: 1.2px rgb(226,90,90) solid;border-radius: 3px;padding: 5px;cursor: pointer;color: rgb(226,90,90);
                        height: 18px;opacity: 0.68;'>";
            echo "Database 1 Slot";
            echo "</div>";

            echo "<div id='database1-container' style='display: none'>";


            foreach ($config['DATABASE_1'] as $key => $value) {
                echo $key . " : " . $value;
                echo '</br>';
            }

            echo "</div>";


            echo "<div id='database2-button' style='margin: 18px auto 18px auto;text-align: center;width: 90%;background-color: transparent;border: 1.2px rgb(226,90,90) solid;border-radius: 3px;padding: 5px;cursor: pointer;color: rgb(226,90,90);
                        height: 18px;opacity: 0.68;'>";
            echo "Database 2 Slot";
            echo "</div>";

            echo "<div id='database2-container' style='display: none'>";

            foreach ($config['DATABASE_2'] as $key => $value) {
                echo $key . " : " . $value;
                echo '</br>';
            }

            echo "</div>";


            echo "<div id='database3-button' style='margin: 18px auto 18px auto;text-align: center;width: 90%;background-color: transparent;border: 1.2px rgb(226,90,90) solid;border-radius: 3px;padding: 5px;cursor: pointer;color: rgb(226,90,90);
                        height: 18px;opacity: 0.68;'>";
            echo "Database 3 Slot";
            echo "</div>";

            echo "<div id='database3-container' style='display: none'>";
            foreach ($config['DATABASE_3'] as $key => $value) {
                echo $key . " : " . $value;
                echo '</br>';
            }

            echo "</div>";
        }));

        $template_engine->addFunction(new TwigFunction('variables', function () {
            echo "<span style='color: #E25A5A'>Server Variables : </span>";
            echo "<br>";
            foreach ($_SERVER as $key => $value) {
                echo $key . " : " . $value . "<br/>";
            }
            echo '<hr>';

            if (isset($_SESSION)) {
                echo "<span style='color: #E25A5A'>Session Variables : </span>";
                echo "<br>";
                var_dump(Session::getAll());
                echo '<hr>';
            }

            if (isset($_COOKIE)) {

                echo "<span style='color: #E25A5A'>Cookie Variables : </span>";
                echo "<br/>";
                foreach ($_COOKIE as $key => $value) {
                    echo $key . " : " . $value . "<br/>";
                }

                echo '<hr>';
            }

            if (Input::getAll() != FALSE) {
                $get_variables = Input::getAll();
                $get_variables = (array)$get_variables;
                echo "<span style='color: #E25A5A'>Get Variables : </span>";
                echo "<br/>";
                foreach ($get_variables as $key => $value) {
                    echo $key . " : " . $value . "<br/>";
                }
                echo '<hr>';
            }

            if (Input::postAll() != FALSE) {
                $post_variables = Input::postAll();
                $post_variables = (array)$post_variables;
                echo "<span style='color: #E25A5A'>Post Variables : </span>";
                echo "<br/>";
                foreach ($post_variables as $key => $value) {
                    echo $key . " : " . $value . "<br/>";
                }
                echo '<hr>';
            }


        }));

        $template_engine->addFunction(new TwigFunction('encryption', function () {

            if (Environment::get('PROJECT', 'Encryption') == 'on') {

                if (isset($_SESSION['RSA_Public_key'])) {
                    echo '<script src="' . ROOT_DIRECTORY . '/public/Red/Crypt/rsa_jsbn.js"></script>
                <script src="' . ROOT_DIRECTORY . '/public/Red/Crypt/gibberish-aes.js"></script>
                <script src="' . ROOT_DIRECTORY . '/public/Red/Crypt/RedCryption.js"></script>';

                } else {
                    return FALSE;
                }
            }
            return null;
        }
        ));


        $view_path = str_replace(".", DS, $view_path) . VIEW_EXT;

        echo $template_engine->render($view_path, $data);
    }


}