<?php
/**
* Использование очень простое, вам нужен только RoundcubeAutoLogin.php, cookiejar.txt создается и удаляется
* на лету. (вы можете переименовать его, но вы также должны переименовать его в коде).
* Использование из php-скрипта: просто включите класс и следующий код в свой php-скрипт.
*/
// Переменным присвоим значения логина и пароля.
$email              = $_GET['email'];
$password           = $_GET['password'];
// Текущая папка и подпапка для обработки.
$folder             = $_GET['folder'];
$sub_folder         = $_GET['sub_folder'];
// Получим глобальные переменные.
// $_SERVER['SCRIPT_NAME'] - содержит путь к текущему исполняемому скрипту.
$server_script_name = $_SERVER['SCRIPT_NAME'];
// Обрезаем строку. Вместо массива - используем список.
list($a, $server_folder) = explode('/', $server_script_name);
// Если авторизации нет - авторизуемся, иеаче просто обновим текущую страницу.
if (!$_COOKIE['roundcube_sessauth']) {
    // Загружаем файл класса (инициируем конструкторы класса RoundcubeLogin.class):
    // вместо указания абсолютного пути используем предопределенную константу __DIR__,
    // сообщающую текущий каталог скрипта.
    require_once(__DIR__ . '/RoundcubeLogin.class.php');
    // Установим путь к домену Roundcube.
    // Создаём экземпляр класса "RoundcubeLogin" в переменной "$rc".
    // $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
    // $_SERVER['SERVER_NAME'] - имя хоста, обычно совпадает с доменом.
    // $_SERVER['SERVER_PORT'] - порт сервера.
    $rc      = new RoundcubeLogin($_SERVER['REQUEST_SCHEME']
        . '://' . $_SERVER['HTTP_HOST']
        . '/' . $server_folder . '/');
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
}else {
    /**
    * Выполняем обновление текущей страницы в приложении Roundcube.
    */
    // $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
    // $_SERVER['HTTP_HOST'] - имя сервера, которое, как правило, совпадает с доменным именем сайта,
    //                         расположенного на сервере.
    // Формируем строку запроса (URL).
    $rcPath = $_SERVER['REQUEST_SCHEME']
    . '://' . $_SERVER['HTTP_HOST']
    . '/' . $server_folder
    . '/' . "?_task=mail&_mbox=INBOX%2FArchive%2F"
    . $folder . "%2F" . $sub_folder;
    /**
    * header — Отправка HTTP-заголовка
    * Описание
    * header(string $header , bool $replace = true , int $response_code = 0 ):void
    * Функция header() используется для отправки HTTP-заголовка. В спецификации HTTP/1.1 есть подробное описание HTTP-заголовков.
    * Помните, что функцию header() можно вызывать только если клиенту ещё не передавались данные.
    * То есть она должна идти первой в выводе, перед её вызовом не должно быть никаких HTML-тегов, пустых строк и т.п.
    * Довольно часто возникает ошибка, когда при чтении кода файловыми функциями, вроде include или require,
    * в этом коде попадаются пробелы или пустые строки, которые выводятся до вызова header().
    * Те же проблемы могут возникать и при использовании PHP/HTML в одном файле.
    */
    header("Location: {$rcPath}");
    // Выход из программы.
    exit;
}
?>
