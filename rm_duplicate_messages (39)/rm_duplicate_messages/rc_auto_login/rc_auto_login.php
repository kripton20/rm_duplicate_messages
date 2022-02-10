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

// Создаём объект входа и включаем отладку.
//$rcl = new RoundcubeLogin(" / roundcube / ", TRUE);

// Получим глобальные переменные:
// Глобальный массив $_SERVER['SCRIPT_NAME'] - содержит путь к текущему исполняемому скрипту.
$server_script_name = $_SERVER['SCRIPT_NAME'];

// Обрезаем строку. Вместо массива - используем список.
list($a, $server_folder) = explode('/', $server_script_name);

// Создаём экземпляр класса "RoundcubeLogin" через переменную "$rcl".
// $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
// $_SERVER['SERVER_NAME'] - имя хоста, обычно совпадает с доменом.
// $_SERVER['SERVER_PORT'] - порт сервера.
$rcl = new RoundcubeLogin($_SERVER['REQUEST_SCHEME']
    . '://' . $_SERVER['HTTP_HOST']
    . '/' . $server_folder . '/', TRUE);

try {
    // Условие проверки передаваемых параметров в массиве "_GET":
    // от этого зависит какую функцию вызываем: авторизация (login()), выход (logout()), обновление страницы (redirect()).
    if ($_GET['email'] & $_GET['password']) {

        // Получим параметры из массива "GET" и создадим переменные:
        // переменным присвоим значения логина и пароля.
        $email          = $_GET['email'];
        $password       = $_GET['password'];

        // 0 - означает - не известно вошли в систему или нет.
        $rcLoginStatus1 = $rcl->rcLoginStatus;

        // $rcLoginStatus2 = FALSE - не вошли в систему.
        $isLoggedIn1 = $rcl->isLoggedIn();

        // $logout = FALSE означает не делать выход из системы.
        $rcl->login($email, $password, $logout = FALSE);

        // 1 - означает - вошли в систему.
        $rcLoginStatus2 = $rcl->rcLoginStatus;
        
        // $rcLoginStatus4 = TRUE - вошли в систему.
        $isLoggedIn2 = $rcl->isLoggedIn();

        // Текущая папка и подпапка для обработки.
        // В условии проверяем если глобальный массив $_GET[] содержит значения: "folder" и "sub_folder":
        // присвоим эти значения переменным "$folder" и "$sub_folder" соответственно.
        $_GET['folder'] ? $folder         = $_GET['folder'] : $folder         = FALSE;
        $_GET['sub_folder'] ? $sub_folder = $_GET['sub_folder'] : $sub_folder = FALSE;

        // Формируем путь: папка и подпапка.
        // в условии проверяем: если переменная "$folder" не равна "FALSE":
        if (!$folder == FALSE) {
        	// Сформируем путь и присвоим значение этой переменной в переменную "$folders".
            $folders = "?_task=mail&_mbox=INBOX%2FArchive%2F" . $folder;
            // Если переменная "$sub_folder" не равна "FALSE":
            if (!$sub_folder == FALSE) {
                // добавим значение этой переменной к переменной "$folders".
                $folders .= "%2F" . $sub_folder;
            }
        }

        // Если вход выполнен - делаем перенаправление:
        // если переменная "$folder" существует - то функцию "redirect()" выполним с параметром,
        // если нет - то функцию "redirect()" выполним без параметра.
        $folder ? $rcl->redirect($folders) : $rcl->redirect();

        // Завершение работы программы.
        exit;

        // В условии проверяем - если вход в систему не выполнен:
        // Проверяем что вернёт функция "isLoggedIn()".
        if (!$rcl->isLoggedIn()) {
            // Выполняем вход в почтовую систему Roundcube и перенаправляем в случае успеха:
            // Вызываем функцию "login()" из класса "RoundcubeLogin".
            $rcl->login($email, $password);
        }else {
            // Если вход выполнен - делаем перенаправление.
            $rcl->redirect();
        }

        // Теперь вы можете установить файлы cookie с помощью функции setcookie php или с помощью
        // любой другой функции используемого вами фреймворка.
        //    foreach ($cookies as $cookie_name => $cookie_value) {
        //        setcookie($cookie_name, $cookie_value, 0, ' / ', '');
        //    };
        // Завершение программы.
        exit;
    }elseif ($_GET('logout')) {
        // Получим параметры из массива "GET" и создадим переменные:
        // переменным присвоим значение передаваемой команды.
        $logout    = $_GET['logout'];
        // Вызываем функцию "logout()" - для выхода из системы Roundcube.
        $login_out = $rcl->logout();
        // Завершение программы.
        exit;
    }elseif ($_GET('redirect')) {
        // Текущая папка и подпапка для обработки.
        $folder     = $_GET['folder'];
        $sub_folder = $_GET['sub_folder'];
        // И перенаправить на roundcube с установленными куками.
        $rcl->redirect($folder, $sub_folder);
        // Проверим ответ от сервера и если нет списка писем - выполним POST - запрос серверу
        // с командой авторизации на сервере.
        // Если переменная "rcLoginStatus" = 1 - значит вход выполнен.
        $ab         = 0;
        /**
        * Выполняем обновление текущей страницы в приложении Roundcube.
        */
        // $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
        // $_SERVER['HTTP_HOST'] - имя сервера, которое, как правило, совпадает с доменным именем сайта,
        //                         расположенного на сервере.
        // Формируем строку запроса (URL).
        $rcPath     = $_SERVER['REQUEST_SCHEME']
        . '://' . $_SERVER['HTTP_HOST']
        . ' / ' . $server_folder
        . ' / ' . "?_task = mail & _mbox = INBOX % 2FArchive % 2F"
        . $folder . " % 2F" . $sub_folder;
        /**
        * header — Отправка HTTP - заголовка
        * Описание
        * header(string $header , bool $replace = TRUE , int $response_code = 0 ):void
        * Функция header() используется для отправки HTTP - заголовка. В спецификации HTTP / 1.1 есть подробное описание HTTP - заголовков.
        * Помните, что функцию header() можно вызывать только если клиенту ещё не передавались данные.
        * То есть она должна идти первой в выводе, перед её вызовом не должно быть никаких HTML - тегов, пустых строк и т.п.
        * Довольно часто возникает ошибка, когда при чтении кода файловыми функциями, вроде include или require,
        * в этом коде попадаются пробелы или пустые строки, которые выводятся до вызова header().
        * Те же проблемы могут возникать и при использовании PHP / HTML в одном файле.
        */
        header("Location: {$rcPath}");
        // Завершение программы.
        exit;
    }

    // Если вход не удался, выводим сообщение об ошибке.
    die("ERROR: ошибка входа в систему из-за неправильной комбинации пользователя и пароля.");
}
catch (RoundcubeLoginException $ex) {
    // Выводим сообщение.
    echo "ERROR: техническая проблема, ".$ex->getMessage();

    // Вызываем функцию "dumpDebugStack()".
    $rcl->dumpDebugStack();

    // Завершение программы.
    exit;
}

?>
