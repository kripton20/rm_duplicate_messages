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
//require_once(__DIR__ . ' / RoundcubeParsing.class.php.php');

// Создаём объект входа и включаем отладку.
//$rcl = new RoundcubeLogin("/roundcube/", true);

// Получим глобальные переменные:
// Глобальный массив $_SERVER['SCRIPT_NAME'] - содержит путь к текущему исполняемому скрипту.
$server_script_name = $_SERVER['SCRIPT_NAME'];
// Обрезаем строку. Вместо массива - используем список.
list($a, $server_folder) = explode('/', $server_script_name);
// Создаём экземпляр класса "RoundcubeLogin" через переменную "$rc".
// $_SERVER['REQUEST_SCHEME'] - схема запроса: http или https.
// $_SERVER['SERVER_NAME'] - имя хоста, обычно совпадает с доменом.
// $_SERVER['SERVER_PORT'] - порт сервера.
$rcl = new RoundcubeLogin($_SERVER['REQUEST_SCHEME']
    . '://' . $_SERVER['HTTP_HOST']
    . '/' . $server_folder . '/', true);

try {
	// Условие проверки передаваемых параметров в массиве "_GET":
// от этого зависит какую функцию вызываем: авторизация (login()), выход (logout()), обновление страницы (redirect()).
if ($_GET['email'] & $_GET['password']) {
    // Получим параметры из массива "GET" и создадим переменные:
    // переменным присвоим значения логина и пароля.
    $email    = $_GET['email'];
    $password = $_GET['password'];
    // Вызываем функцию "login()" из класса "RoundcubeLogin".
    // Вызываем функцию "rc_auto_login_relogin()":
    // - запус всей процедуры автоматической авторизации.
    //$cookies  = $rc->login($email, $password);
        // В условии проверяем - если вход в систему уже выполнен:
    if ($rcl->isLoggedIn())
    
    // Тогда делаем перенаправление.
    $rcl->redirect();

    // Если нет - попробуем войти в систему и перенаправляем в случае успеха.
    $rcl->login("your-email-address", "plain-text-password");
    
    // Теперь вы можете установить файлы cookie с помощью функции setcookie php или с помощью
    // любой другой функции используемого вами фреймворка.
//    foreach ($cookies as $cookie_name => $cookie_value) {
//        setcookie($cookie_name, $cookie_value, 0, '/', '');
//    };
    // Завершение программы.
    exit;
}elseif ($_GET('logout')) {
    // Получим параметры из массива "GET" и создадим переменные:
    // переменным присвоим значение передаваемой команды.
    $logout    = $_GET['logout'];
    // Вызываем функцию "logout()" - для выхода из системы Roundcube.
    $login_out = $rc->logout();
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
    * header(string $header , bool $replace = true , int $response_code = 0 ):void
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





// Объявление защищённого метода - "protected function".
// К protected (защищенным) свойствам и методам можно получить доступ либо из содержащего их
// класса, либо из его подкласса. Никакому внешнему коду доступ к ним не предоставляется.
// Запечатаем функцию "file_put_contents" в нами созданную функцию "write_log_file" с параметром "args".
function write_log_file ($args)
{
    // Пишем содержимое (строку) в файл,
    // используя флаг FILE_APPEND flag для дописывания содержимого в конец файла и флаг LOCK_EX
    // для предотвращения записи данного файла кем - нибудь другим в данное время.
    /**
    * Функция записи отладочной информации в log-файл.
    * file_put_contents — Пишет данные в файл
    * file_put_contents(string $filename , mixed $data , int $flags = 0 , resource $context = ?):int
    * Функция идентична последовательным успешным вызовам функций fopen(), fwrite() и fclose().
    * Если filename не существует, файл будет создан. Иначе, существующий файл будет
    * перезаписан, за исключением случая, если указан флаг FILE_APPEND.
    * @param filename   Путь к записываемому файлу.
    * @param data       Записываемые данные. Может быть типа string, array или ресурсом потока.
    *                   Если data является потоковым ресурсом (stream), оставшийся буфер этого потока
    *                   будет скопирован в указанный файл.
    *                   Это похоже на использование функции stream_copy_to_stream().
    *                   Также вы можете передать одномерный массив в качестве параметра data.
    *                   Это будет эквивалентно вызову file_put_contents($filename, implode('', $array)).
    * @param flags      Значением параметра flags может быть любая комбинация следующих флагов,
    *                   соединённых бинарным оператором ИЛИ (|).
    * Доступные флаги:  FILE_USE_INCLUDE_PATH - Ищет filename в подключаемых директориях. Подробнее смотрите
    * директиву include_path; FILE_APPEND - Если файл filename уже существует, данные будут дописаны в конец
    * файла вместо того, чтобы его перезаписать; LOCK_EX - Получить эксклюзивную блокировку на файл на время
    * записи. Другими словами, между вызовами fopen() и fwrite() произойдёт вызов функции flock().
    * Это не одно и то же, что вызов fopen() с флагом "x".
    */
    file_put_contents(
        'D:\WEB\X5\tmp\logs\rm_session_debug.log',
        /**
        * print_r — Выводит удобочитаемую информацию о переменной.
        * Если вы хотите перехватить вывод print_r(), используйте параметр return. Если его значение равно true,
        * то print_r() вернёт информацию вместо вывода в браузер.
        */
        print_r($args, true),
        FILE_APPEND | LOCK_EX
    );
}
?>
