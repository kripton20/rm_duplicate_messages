<?php
/**
* Установка:
* 1) создайте папку - disablecsrf внутри каталога plugins/.
* 2) поместите туда этот файл и назовите его - disablecsrf.php
* 3) перейдите в config/config.inc.php и добавьте его в плагины.
* Пример:
*        $config['plugins'] = array('disablecsrf');
*
* Теперь CSRF должен быть отключен для входа в систему.
*/

/**
* Пример плагина для отключения CSRF для почты RoundCube (проверено только на 1.0.3)
*
* @license MIT
* @author huglester@gmail.com
*/
class disablecsrf extends rcube_plugin
{
    public $task = 'login';

    function init()
    {
        $this->add_hook('startup', array($this,'startup'));
        $this->add_hook('authenticate', array($this,'authenticate'));
        $this->add_hook('msg_request', array($this,'msg_request'));
    }

    function authenticate($args)
    {
        $args['cookiecheck'] = false;
        $args['valid'] = true;

        return $args;
    }
    
        function msg_request($args)
    {
        $args['cookiecheck'] = false;
        $args['valid'] = true;

        return $args;
    }
}
?>