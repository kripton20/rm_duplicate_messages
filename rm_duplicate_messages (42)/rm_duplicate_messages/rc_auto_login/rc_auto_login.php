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

// Получим глобальные переменные:
// Глобальный массив $_SERVER['SCRIPT_NAME'] - содержит путь к текущему исполняемому скрипту.
// Обрезаем строку: вместо массива - используем список состоящий из двух элементов (первый элемент - не обязательный).
list(, $server_folder) = explode('/', $_SERVER['SCRIPT_NAME']);

// Создаём экземпляр класса "RoundcubeLogin" через переменную "$rcl", и включаем отладку.
// $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
// $_SERVER['HTTP_HOST'] - имя сервера, которое, как правило, совпадает
//                         с доменным именем сайта, расположенного на сервере.
$rcl = new RoundcubeLogin($_SERVER['REQUEST_SCHEME']
    . '://' . $_SERVER['HTTP_HOST']
    . '/' . $server_folder . '/', TRUE);

// Выполняем все операции в обработчике ошибок.
try {
    // Если глобальный массив GET[] содержит "logout" - вызываем функцию "logout()" - выход.
    if (isset($_GET['logout'])) {
        // Проверяем что вернёт функция "isLoggedIn()":
        // если TRUE - вход в Roundcube выполнен, если FALSE - вход в Roundcube не выполнен, и тогда выполняем вход.
        // Если вход уже выполнен - делаем перенаправление в приложение Roundcube.
        if ($rcl->isLoggedIn()) {
            // Получим параметры из массива "GET" и создадим переменные:
            // переменным присвоим значение передаваемой команды.
            //$logout = $_GET['logout'];
            // Вызываем функцию "logout()" - для выхода из системы Roundcube.
            $rcl->logout();
        }
        // Завершение работы программы.
        exit;

        // Условие проверки передаваемых параметров в глобальном массиве "_GET":
        // от этого зависит какую функцию вызываем: авторизация - "login()" или выход - "logout()".
        // Если глобальный массив GET[] содержит "email" и "password" - вызываем функцию "login()" - авторизация.
    }elseif (isset($_GET['email']) & isset($_GET['password'])) {
        // Проверяем что вернёт функция "isLoggedIn()":
        // если TRUE - вход в Roundcube выполнен, если FALSE - вход в Roundcube не выполнен, и тогда выполняем вход.
        // Если вход уже выполнен - делаем перенаправление в приложение Roundcube.
        if ($rcl->isLoggedIn()) {
            // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
            // тогда вызываем функцию "create_path_folders()".
            if (isset($_GET['folder'])) $path_folders = create_path_folders();

            // Если вход выполнен - делаем перенаправление в приложение Roundcube:
            // Формируем строку запроса (URL) и выполняем обновление страницы:
            if (isset($path_folders)) {
            	// Если переменная "$folder" существует - то функцию "redirect()" выполним с параметром.
                $rcl->redirect($path_folders);
            }else {
            	// Если нет - то функцию "redirect()" выполним без параметра.
                $rcl->redirect();
            }
            // Завершение работы программы.
            exit;

            // Иначе выполняем авторизацию.
        }else {
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
                // Запишем сообщение в лог - файл: вызываем функцию "write_log_file()" из класса "RoundcubeLogin" с аргументом "$args".
                RoundcubeLogin::write_log_file($args);
            }

            // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
            // тогда вызываем функцию "create_path_folders()".
            if (isset($_GET['folder'])) $path_folders = create_path_folders();

            // Если вход выполнен - делаем перенаправление в приложение Roundcube:
            // Формируем строку запроса (URL) и выполняем обновление страницы:
            if (isset($path_folders)) {
            	// Если переменная "$folder" существует - то функцию "redirect()" выполним с параметром.
                $rcl->redirect($path_folders);
            }else {
            	// Если нет - то функцию "redirect()" выполним без параметра.
                $rcl->redirect();
            }
            // Завершение работы программы.
            exit;
        }
    }else {
        // Если вход не удался, выводим сообщение об ошибке.
        die("<b>ERROR: </b><br /><br />Вы не указали параметры!");
    }

}
catch (RoundcubeLoginException $ex) {
    // Вызываем функцию "dumpDebugStack()".
    $rcl->dumpDebugStack();
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
    //if (isset($_GET['folder'])) {
        // Сформируем путь и присвоим значение этой переменной в переменную "$folders".
        $path_folders = "?_task=mail&_mbox=INBOX%2FArchive%2F" . $_GET['folder'];
        // Если переменная "$sub_folder" не равна "FALSE":
        //if (!$sub_folder == FALSE) {
        if (isset($_GET['sub_folder'])) {
            // добавим значение этой переменной к переменной "$folders".
            $path_folders .= "%2F" . $_GET['sub_folder'];
        }
    //}
    // Возвращаем сформированный путь из папок.
    return $path_folders;
}
?>
