<?php
/**
* Использование очень простое, вам нужен только RoundcubeAutoLogin.php, cookiejar.txt создается и удаляется
* на лету. (вы можете переименовать его, но вы также должны переименовать его в коде).
* Использование из php-скрипта: просто включите класс и следующий код в свой php-скрипт.
*/
// Загрузка класса:
// вместо указания абсолютного пути используем предопределенную константу __DIR__,
// сообщающую текущий каталог скрипта.
require_once(__DIR__ . '/RoundcubeLogin.class.php');

// Получаем данные из глобального массива "post".
$email= $_POST['rc_user'];
$password= $_POST['rc_password'];

// Установим путь к домену Roundcube.
//$rc      = new RoundcubeLogin('http://localhost/rc147/');
//$rc      = new RoundcubeLogin($_SERVER['SERVER_NAME'] . /rc147/);
$rc      = new RoundcubeLogin(cadmail/mail/);
//$cookies = $rc->login($email, $password);
$cookies = $rc->login('l51@niiemp.local', 'l51v6249niiemp');
// Теперь вы можете установить файлы cookie с помощью функции setcookie php или с помощью
// любой другой функции используемого вами фреймворка.
foreach ($cookies as $cookie_name => $cookie_value) {
    setcookie($cookie_name, $cookie_value, 0, '/', '');
}
// И перенаправить на roundcube с установленными куками.
$rc->redirect();
?>
