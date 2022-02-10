<?php
/**
* Использование: вам нужен только RoundcubeAutoLogin.php, cookiejar.txt создается и удаляется на лету.
* Использование из php-скрипта: включите класс и следующий код в свой php-скрипт и сделайте вызов функций.
*/
/**
* Выражение require_once аналогично require за исключением того, что PHP проверит, включался-ли уже данный файл,
* и если да, не будет включать его ещё раз.
*
* Вместо указания абсолютного пути используем предопределенную константу __DIR__, сообщающую текущий каталог скрипта.
*/

// Загружаем файл класса "RoundcubeLogin" - инициируем конструкторы класса.
require_once(__DIR__ . '/RoundcubeLogin.class.php');
// Загружаем файл класса "RoundcubeParsing" - инициируем конструкторы класса.
//require_once(__DIR__ . ' / CLI_MSG_Request.class.php');

// Получим глобальные переменные:
// Глобальный массив $_SERVER['SCRIPT_NAME'] - содержит путь к текущему исполняемому скрипту.
//$server_script_name = $_SERVER['SCRIPT_NAME'];

// Обрезаем строку. Вместо массива - используем список.
list($a, $server_folder) = explode('/', $_SERVER['SCRIPT_NAME']);

// Создаём экземпляр класса "RoundcubeLogin" через переменную "$rcl", и включаем отладку.
// $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
// $_SERVER['HTTP_HOST'] - имя сервера, которое, как правило, совпадает
//                         с доменным именем сайта, расположенного на сервере.
$rcl = new RoundcubeLogin($_SERVER['REQUEST_SCHEME']
    . '://' . $_SERVER['HTTP_HOST']
    . '/' . $server_folder . '/', TRUE);

// Выполняем все операции в обработчике ошибок.
try {
    // Проверяем что вернёт функция "isLoggedIn()":
    // если TRUE - вход в Roundcube выполнен, если FALSE - вход в Roundcube не выполнен, и тогда выполняем вход.
    if (!$rcl->isLoggedIn()) {
        // Условие проверки передаваемых параметров в массиве "_GET":
        // от этого зависит какую функцию вызываем: авторизация (login()), выход (logout()), обновление страницы (redirect()).
        if ($_GET['email'] & $_GET['password']) {

            // Получим параметры из массива "GET" и создадим переменные:
            // переменным присвоим значения логина и пароля.
            $email    = $_GET['email'];
            $password = $_GET['password'];

            // Выполняем вход в почтовую систему Roundcube и перенаправляем в случае успеха:
            // Вызываем функцию "login()" из класса "RoundcubeLogin".
            // $logout = FALSE означает не делать выход из системы.
            $rcl->login($email, $password, $logout   = FALSE);

            // В условии проверяем если включен режим отладки: Вызываем функцию записи в лог - файл.
            if ($rcl->debugEnabled == TRUE) {
                // формируем данные для записи в лог - файл.
                $args = "COOKIE['PHPSESSID'] = " . $_COOKIE['PHPSESSID'] . "\r\n";
                $args .= "COOKIE['roundcube_sessid'] = " . $_COOKIE['roundcube_sessid'] . "\r\n";
                $args .= "COOKIE['roundcube_sessauth'] = " .  $_COOKIE['roundcube_sessauth'] . "\r\n";
                $args .= "lastToken = " .  $rcl->lastToken . "\r\n";
                $args .= "rcLoginStatus = " .  $rcl->rcLoginStatus . "\r\n";
                $args .= "rcSessionID = " .  $rcl->rcSessionID . "\r\n\n";
                // Запишем сообщение в лог - файл.
                //$rcl->write_log_file($args);
                RoundcubeLogin::write_log_file($args);
            }

            // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
            // тогда вызываем функцию "create_path_folders()".
            if ($_GET['folder']) $path_folders = create_path_folders();

            // Если вход выполнен - делаем перенаправление:
            // если переменная "$folder" существует - то функцию "redirect()" выполним с параметром,
            // если нет - то функцию "redirect()" выполним без параметра.
            $path_folders ? $rcl->redirect($path_folders) : $rcl->redirect();

            // Завершение работы программы.
            exit;
        }
    }else {

        // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
        // тогда вызываем функцию "create_path_folders()".
        if ($_GET['folder']) $path_folders = create_path_folders();

        // Если вход выполнен - делаем перенаправление:
        // если переменная "$folder" существует - то функцию "redirect()" выполним с параметром,
        // если нет - то функцию "redirect()" выполним без параметра.
        $path_folders ? $rcl->redirect($path_folders) : $rcl->redirect();

        // Завершение работы программы.
        exit;
    }
    //    }elseif ($_GET('logout')) {
    //        // Получим параметры из массива "GET" и создадим переменные:
    //        // переменным присвоим значение передаваемой команды.
    //        $logout = $_GET['logout'];
    //        // Вызываем функцию "logout()" - для выхода из системы Roundcube.
    //        $login_out = $rcl->logout();
    //        // Завершение программы.
    //        exit;
    //    }elseif ($_GET('redirect')) {
    //        // Текущая папка и подпапка для обработки.
    //        $folder = $_GET['folder'];
    //        $sub_folder = $_GET['sub_folder'];
    //        // И перенаправить на roundcube с установленными куками.
    //        $rcl->redirect($folder, $sub_folder);
    //        // Проверим ответ от сервера и если нет списка писем - выполним POST - запрос серверу
    //        // с командой авторизации на сервере.
    //        // Если переменная "rcLoginStatus" = 1 - значит вход выполнен.
    //        $ab = 0;
    //        /**
    // * Выполняем обновление текущей страницы в приложении Roundcube.
    //        */
    //        // $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
    //        // $_SERVER['HTTP_HOST'] - имя сервера, которое, как правило, совпадает с доменным именем сайта,
    //        //                         расположенного на сервере.
    //        // Формируем строку запроса (URL).
    //        $rcPath = $_SERVER['REQUEST_SCHEME']
    //        . '://' . $_SERVER['HTTP_HOST']
    //        . ' / ' . $server_folder
    //        . ' / ' . "?_task = mail & _mbox = INBOX % 2FArchive % 2F"
    //        . $folder . " % 2F" . $sub_folder;
    //        /**
    // * header — Отправка HTTP - заголовка
    // * Описание
    // * header(string $header , bool $replace = TRUE , int $response_code = 0 ):void
    // * Функция header() используется для отправки HTTP - заголовка. В спецификации HTTP / 1.1 есть подробное описание HTTP - заголовков.
    // * Помните, что функцию header() можно вызывать только если клиенту ещё не передавались данные.
    // * То есть она должна идти первой в выводе, перед её вызовом не должно быть никаких HTML - тегов, пустых строк и т.п.
    // * Довольно часто возникает ошибка, когда при чтении кода файловыми функциями, вроде include или require,
    // * в этом коде попадаются пробелы или пустые строки, которые выводятся до вызова header().
    // * Те же проблемы могут возникать и при использовании PHP / HTML в одном файле.
    //        */
    //        header("Location: {$rcPath}");
    //        // Завершение программы.
    //        exit;
    //    }


}
catch (RoundcubeLoginException $ex) {
    // Вызываем функцию "dumpDebugStack()".
    $rcl->dumpDebugStack();
    // Выводим сообщение.
    //echo ;
    // Если вход не удался, выводим сообщение об ошибке.
    die("<b>ERROR: </b><br /><br />" . $ex->getMessage());
}

// Функция формирует папки почтового ящика для перенаправления.
function create_path_folders()
{
    // Текущая папка и подпапка для обработки.
    // В условии проверяем если глобальный массив $_GET[] содержит значения: "folder" и "sub_folder":
    // присвоим эти значения переменным "$folder" и "$sub_folder" соответственно.
    //$_GET['folder'] ? $folder = $_GET['folder'] : $folder = FALSE;
    //$_GET['sub_folder'] ? $sub_folder = $_GET['sub_folder'] : $sub_folder = FALSE;

    // Формируем путь: папка и подпапка.
    // в условии проверяем: если переменная "$folder" не равна "FALSE":
    //if (!$folder == FALSE) {
    if (isset($_GET['folder'])) {
        // Сформируем путь и присвоим значение этой переменной в переменную "$folders".
        $path_folders = "?_task=mail&_mbox=INBOX%2FArchive%2F" . $_GET['folder'];
        // Если переменная "$sub_folder" не равна "FALSE":
        //if (!$sub_folder == FALSE) {
        if (isset($_GET['sub_folder'])) {
            // добавим значение этой переменной к переменной "$folders".
            $path_folders .= "%2F" . $_GET['sub_folder'];
        }
    }
    // Возвращаем сформированный путь из папок.
    return $path_folders;
}
?>
