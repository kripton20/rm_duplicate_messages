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

// Загружаем файл класса "RoundcubeLogin" - инициируем конструкторы класса.Commands

// Получим глобальные переменные:
// Глобальный массив $_SERVER['SCRIPT_NAME'] - содержит путь к текущему исполняемому скрипту.
// Обрезаем строку: вместо массива - используем список состоящий из двух элементов (первый элемент - не обязательный).
list(, $server_folder) = explode('/', $_SERVER['SCRIPT_NAME']);

// Создаём экземпляр класса "RoundcubeLogin" через переменную "$rcl", и передаём следующие параметры:
// 1 параметр: $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
// 2 параметр: $_SERVER['HTTP_HOST'] - имя сервера, которое, как правило, совпадает
//                         с доменным именем сайта, расположенного на сервере.
// 3 параметр: $server_folder - содержит путь к папке где распологается Roundcube.
// 4 параметр: если - TRUE - включаем отладку.
// 5 параметр: если - TRUE - включаем запись отладки в лог - файл.
// 6 параметр: если - TRUE - включаем перенаправление в Roundcube для браузера.
$rcl = new RoundcubeLogin($_SERVER['REQUEST_SCHEME']
    . '://' . $_SERVER['HTTP_HOST']
    . '/' . $server_folder . '/', TRUE, TRUE, FALSE);

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
        // В условии проверяем если включен режим отладки: вызываем функцию записи в лог - файл.
        if ($rcl->debugEnabled == TRUE & $rcl->writeLogEnabled == TRUE) set_params_logfile($rcl);
    }
    // Условие проверки передаваемых параметров в глобальном массиве "_GET":
    // от этого зависит какую функцию вызываем: авторизация - "login()" или выход - "logout()".
    // Если глобальный массив GET[] содержит "email" и "password" - вызываем функцию "login()" - авторизация.
    elseif (isset($_GET['email']) & isset($_GET['password'])) {
        // Проверяем что вернёт функция "isLoggedIn()":
        // если TRUE - вход в Roundcube выполнен, если FALSE - вход в Roundcube не выполнен, и тогда выполняем вход.
        // Если вход уже выполнен - делаем перенаправление в приложение Roundcube.
        if ($rcl->isLoggedIn()) {
            // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
            // тогда вызываем функцию "create_path_folders()".
            if (isset($_GET['folder'])) $path_folders = create_path_folders();

            // В условии проверяем если включен режим перенаправления в Roundcube для браузера.
            // Тогда вызываем функцию sent_redirect().
            if ($rcl->sentRedirectEnabled == TRUE) sent_redirect($rcl, $path_folders);

            // В условии проверяем если включен режим отладки (debugEnabled = TRUE) и указание
            // записи в лог - файл (writeLogEnabled = TRUE): вызываем функцию записи в лог - файл.
            if ($rcl->debugEnabled == TRUE & $rcl->writeLogEnabled == TRUE) set_params_logfile($rcl);

            // Функция отправляет команду WEB - серверу которую должен выполнить Roundcube.
            //msg_request($rcl);
        }
        // Иначе выполняем авторизацию.
        else {
            // Получим параметры из массива "GET" и создадим переменные:
            // переменным присвоим значения логина и пароля.
            $email    = $_GET['email'];
            $password = $_GET['password'];

            // Выполняем вход в почтовую систему Roundcube и перенаправляем в случае успеха:
            // Вызываем функцию "login()" из класса "RoundcubeLogin".
            // $logout = FALSE означает не делать выход из системы.
            $rcl->login($email, $password, $logout   = FALSE);

            // В условии проверяем: если глобальный массив $_GET[] содержит "folder":
            // тогда вызываем функцию "create_path_folders()".
            if (isset($_GET['folder'])) $path_folders = create_path_folders();

            // В условии проверяем если включен режим перенаправления в Roundcube для браузера.
            // Тогда вызываем функцию sent_redirect().
            if ($rcl->sentRedirectEnabled == TRUE) sent_redirect($rcl, $path_folders);

            // В условии проверяем если включен режим отладки (debugEnabled = TRUE) и указание
            // записи в лог - файл (writeLogEnabled = TRUE): вызываем функцию записи в лог - файл.
            if ($rcl->debugEnabled == TRUE & $rcl->writeLogEnabled == TRUE) set_params_logfile($rcl);

            // Функция отправляет команду WEB - серверу которую должен выполнить Roundcube.
            //msg_request($rcl);
        }
    }
    // Иначе если вход не удался.
    else {
        // Выводим сообщение об ошибке.
        die("<b>ERROR: </b><br /><br />Вы не указали параметры!");
    }
    // Завершение работы программы.
    exit;
}
catch (RoundcubeLoginException $ex) {
    // Вызываем функцию "dumpDebugStack()".
    $rcl->dumpDebugStack();
    // Если вход не удался, выводим сообщение об ошибке.
    die("<b>ERROR: </b><br /><br />" . $ex->getMessage());
}

/**
* Функция отправляет команду WEB-серверу которую должен выполнить Roundcube.
*/
function msg_request($rcl)
{
    // Формируем строку данных для POST - запроса.
    //$data = (($rcl->lastToken) ? "_token=".$rcl->lastToken."&" : "")

    // Команда передаваемая приложению Roundcube для выполнения: действие и задача - "logout".
    //. "_action=plugin.restart_msg_request";
    //. "_action=plugin.msg_request";
    $data = "_task=mail&_action=plugin.msg_request";

    // Вызываем функцию "sendRequest()" - отправляем запрос серверу.
    $rcl->sendRequest($rcl->rcPath, $data);

    // Вызываем функцию "isLoggedIn()" и вернём её отрицательное значение.
    //$a= !$rcl->isLoggedIn();
    //$a= $rcl->isLoggedIn();
    //return !$rcl->isLoggedIn();
}

// Функция формирует папки почтового ящика для перенаправления.
function create_path_folders()
{
    // Формируем путь: папка и подпапка для перехода в конкретную папку.
    // Сформируем путь и присвоим значение массива $_GET['sub_folder'] в переменную "$path_folders".
    $path_folders = "?_task=mail&_mbox=INBOX%2FArchive%2F" . $_GET['folder'];
    // В условии проверяем если глобальный массив $_GET[] содержит "$path_folders":
    if (isset($_GET['sub_folder'])) {
        // добавим значение этой переменной к переменной "$folders".
        $path_folders .= "%2F" . $_GET['sub_folder'];
    }
    // Возвращаем сформированный путь из папок.
    return $path_folders;
}

function set_params_logfile($rcl)
{
    // Инициализируем переменную "$args" - присвоим ей пустую строку.
    $args = "";
    // формируем данные для записи в лог - файл.
    //$args = "COOKIE['PHPSESSID'] = " . $_COOKIE['PHPSESSID'] . "\r\n";
    $args .= "COOKIE['roundcube_sessid'] = " . $_COOKIE['roundcube_sessid'] . "\r\n";
    $args .= "COOKIE['roundcube_sessauth'] = " .  $_COOKIE['roundcube_sessauth'] . "\r\n";
    $args .= "lastToken = " .  $rcl->lastToken . "\r\n";
    $args .= "rcLoginStatus = " .  $rcl->rcLoginStatus . "\r\n";
    $args .= "rcSessionID = " .  $rcl->rcSessionID . "\r\n\n";
    // Запишем сообщение в лог - файл: вызываем функцию "write_log_file()" из класса "RoundcubeLogin" с аргументом "$args".
    RoundcubeLogin::write_log_file($args);
}

function sent_redirect($rcl, $path_folders)
{
    // Если вход выполнен - делаем перенаправление в приложение Roundcube:
    // - формируем строку запроса (URL) и выполняем обновление страницы.
    // Условие проверки существования переменной "$path_folders":
    if (isset($path_folders)) {
        // Если переменная "$path_folders" существует - включаем её в состав URL,
        // и функцию "redirect()" выполним с параметром.
        $rcl->redirect($path_folders);
    }
    // Иначе если вход не выполнен.
    else {
        // Функцию "redirect()" выполним без параметра.
        $rcl->redirect();
    }
}
?>
