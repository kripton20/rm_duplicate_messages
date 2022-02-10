<?php
/**
* Использование очень простое, вам нужен только RoundcubeAutoLogin.php, cookiejar.txt создается и удаляется
* на лету. (вы можете переименовать его, но вы также должны переименовать его в коде).
* Использование из php-скрипта: просто включите класс и следующий код в свой php-скрипт.
*/
// Переменным присвоим значения логина и пароля.
//$email              = 'ocik@niiemp.local';
//$password           = 'ocik1905niiemp';
$email=$_GET['email'];
$password=$_GET['password'];

// Текущая папка для обработки.
//$folder             = "Inbox2020";
//$sub_folder         = "2020_1";
$folder=$_GET['folder'];
$sub_folder=$_GET['sub_folder'];

// Получим глобальные переменные.
//$server_request_scheme = $_SERVER['REQUEST_SCHEME'];
//$server_name = $_SERVER['SERVER_NAME'];
//$server_port = $_SERVER['SERVER_PORT'];
$server_script_name = $_SERVER['SCRIPT_NAME'];

// Обрезаем строку. Вместо массива - используем список.
list($a, $server_folder) = explode('/', $server_script_name);

// Загружаем файл класса (инициируем конструкторы класса RoundcubeLogin.class):
// вместо указания абсолютного пути используем предопределенную константу __DIR__,
// сообщающую текущий каталог скрипта.
require_once(__DIR__ . '/RoundcubeLogin.class.php');

// Установим путь к домену Roundcube.
// Создаём экземпляр класса "RoundcubeLogin" в переменной "$rc".
$rc      = new RoundcubeLogin($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . '/' . $server_folder . '/');
//$rc2 = new RoundcubeLogin($server_request_scheme . '://' . $server_name . ':' . $server_port . ' / ' . $server_folder . ' / ');

// Получим cookies:
// Вызываем функцию "login()" из класса "RoundcubeLogin" в переменной "$rc".
$cookies = $rc->login($email, $password);

// Теперь вы можете установить файлы cookie с помощью функции setcookie php или с помощью
// любой другой функции используемого вами фреймворка.
foreach ($cookies as $cookie_name => $cookie_value) {
    setcookie($cookie_name, $cookie_value, 0, '/', '');
}
// И перенаправить на roundcube с установленными куками.
$rc->redirect($folder, $sub_folder);
?>
