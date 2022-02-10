<?php
/**
* Использование очень простое, вам нужен только RoundcubeAutoLogin.php, cookiejar.txt создается и удаляется
* на лету. (вы можете переименовать его, но вы также должны переименовать его в коде).
* Использование из php-скрипта: просто включите класс и следующий код в свой php-скрипт.
*/
function rc_auto_login()
{
    // Загружаем файл класса:
    // вместо указания абсолютного пути используем предопределенную константу __DIR__,
    // сообщающую текущий каталог скрипта.
    require_once(__DIR__ . '/RoundcubeLogin.class.php');

    // Получаем данные из глобального массива "post".
    //$email = $_POST['rc_user'];
    //$password = $_POST['rc_password'];
    $email                 = 'ocik@niiemp.local';
    $password              = 'ocik1905niiemp';

    // Получим глобальные переменные.
    $server_request_scheme = $_SERVER['REQUEST_SCHEME'];
    $server_name           = $_SERVER['SERVER_NAME'];
    $server_port           = $_SERVER['SERVER_PORT'];
    $server_script_name    = $_SERVER['SCRIPT_NAME'];

    // Обрезаем строку. Вместо массива - используем список.
    list($a, $server_folder) = explode('/', $server_script_name);
    list($server_folder, $c) = explode('/', $server_folder);

    // Установим путь к домену Roundcube.
    //$rc = new RoundcubeLogin('http://localhost / rc147 / ');
    $rc      = new RoundcubeLogin($server_request_scheme . '://' . $server_name . '/' . $server_folder . '/');

    // Получим cookies.
    //$rc = new RoundcubeLogin(cadmail / mail / );
    $cookies = $rc->login($email, $password);

    // Теперь вы можете установить файлы cookie с помощью функции setcookie php или с помощью
    // любой другой функции используемого вами фреймворка.
    foreach ($cookies as $cookie_name => $cookie_value) {
        setcookie($cookie_name, $cookie_value, 0, '/', '');
    }
    // И перенаправить на roundcube с установленными куками.
    $rc->redirect();
}
?>
