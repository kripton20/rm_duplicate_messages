<?php
/**
* Справка по использованию curl mk:@MSITStore:D:\WEB\Programs\CodelobsterSoftware\CodelobsterPHPEdition_5.15\Data\ContextHelp\php_enhanced_ru.chm::/res/ref.curl.html
* * Используйте сценарий PHP для входа в почтовую систему Roundcube.
*
* ВЕРСИЯ СКРИПТА
* Версия 4 (июль 2013 г.)
*
* ЗАГРУЗКА И ДОКУМЕНТАЦИЯ
* http://blog.philippheckel.com/2008/05/16/roundcube-login-via-php-script/
*
* ТРЕБОВАНИЯ
* - Установка Roundcube (проверено с 0.9.2)
* (более старые версии работают с 0.2-beta, 0.3.x, 0.4-beta, 0.5, 0.5.1, 0.7.2)
*
* - Установите для "check_ip"/"ip_check" в файле config/main.inc.php значение FALSE.
*     Почему? Сервер будет выполнять вход в систему, а не клиент (два разных IP-адреса)
*
* УСТАНОВКА
* - Установите RC на свой сервер, чтобы к нему можно было получить доступ через браузер,
* например, на www.example.com/roundcube/
*
* - Загрузите этот скрипт и удалите все пробелы и новые строки
* до «<?php» и после «?>»
*
* - Включите класс в свой собственный сценарий и используйте его.
*
* ИСПОЛЬЗОВАНИЕ
* Класс предоставляет четыре общедоступных метода:
*
*   - login($username, $password)
* Выполните вход в почтовую систему Roundcube.
*
* Примечание: если клиент уже вошел в систему, сценарий повторно авторизует пользователя (выход/вход).
* Чтобы предотвратить такое поведение, используйте функцию isLoggedIn().
*
* Возвращает: TRUE, если вход в систему прошел успешно, FALSE, если комбинация пользователь/пароль неверна.
* Броски: может вызвать исключение RoundcubeLoginException, если Roundcube отправляет неожиданный ответ
* (это может произойти, если новая версия Roundcube будет вести себя иначе).
*
* - isLoggedIn()
* Проверяет, вошел ли клиент/браузер в систему и есть ли действительный сеанс Roundcube.
*
* Возвращает: TRUE, если пользователь вошел в систему, в противном случае - FALSE.
* Броски: может также вызвать исключение RoundcubeLoginException (см. Выше).
*
*   - logout()
* Выполняет выход из текущего сеанса Roundcube.
*
* Возвращает: TRUE, если выход прошел успешно, в противном случае - FALSE.
* Броски: может также вызвать исключение RoundcubeLoginException (см. Выше).
*
*   - redirect()
* Просто перенаправляет на Roundcube.
*
* - setHostname($hostname)
* Заменить имя хоста по умолчанию. Возможен только локальный хост.
*
*   - setPort($port)
* Отменить автоматическое определение порта.
*
* - setSSL($enableSSL)
* Отменить автоматическое обнаружение SSL.
*
* ОБРАЗЕЦ КОДА АВТОМАТИЧЕСКОЙ АВТОРИЗАЦИИ
*   <?php
* include "RoundcubeLogin.class.php";
* 
* 
* // Создаём объект входа и включаем отладку.
* $rcl = new RoundcubeLogin("/roundcube/", true);
* 
* try {
*     // Если мы уже вошли в систему - то просто перенаправляем.
*     if ($rcl->isLoggedIn())
*     $rcl->redirect();
* 
*     // Если нет - попробуем войти в систему и перенаправляем в случае успеха.
*     $rcl->login("your-email-address", "plain-text-password");
* 
*     // Если вход не удался, выводим сообщение об ошибке.
*     die("ERROR: ошибка входа в систему из-за неправильной комбинации пользователя и пароля.");
* }
* catch (RoundcubeLoginException $ex) {
*     echo "ERROR: техническая проблема, ".$ex->getMessage();
*     $rcl->dumpDebugStack();
*     exit;
* }
*   ?>
*
* ИСПРАВЛЕНИЕ ПРОБЛЕМ
* - Обязательно удалите все пробелы перед "<?php" и после "?>"
* - включить режим отладки (установить второй параметр конструктора в TRUE)
* - Спросите меня, есть ли у вас проблемы :-)
*
* АВТОР/ЛИЦЕНЗИЯ/ВЕРСИЯ
* - Автор Филипп Хекель; Найдите соответствующую запись в блоге по адресу
*     http://blog.philippheckel.com/2008/05/16/roundcube-login-via-php-script/
*
* - Обновлено в июле 2013 г., протестировано с Ubuntu/Firefox 3
* Нет лицензии. Не стесняйтесь использовать :-)
*
* - Обновленный скрипт протестирован с Roundcube 0.9.2.
* Старые версии скрипта работают с Roundcube 0.2, 0.3, 0.4-beta,
* 0.5.1 и 0.7.2 (см. Сообщение в блоге выше)
*
*/
class RoundcubeLogin
{
    /**
    * Relative path to the Roundcube base directory on the server.
    *
    * Can be set via the first argument in the constructor.
    * If the URL is www.example.com/roundcube/, set it to "/roundcube/".
    *
    * @var string
    */
    private $rcPath;

    /**
    * Roundcube session ID
    *
    * RC sends its session ID in the answer. If the first attempt doesn't
    * work, the login-function retries it with the session ID. This does
    * work most of the times.
    *
    * @var string
    */
    private $rcSessionID;

    /**
    * No idea what this is ...
    */
    private $rcSessionAuth;

    /**
    * Save the current status of the Roundcube session.
    * 0 = unkown, 1 = logged in, -1 = not logged in.
    *
    * @var int
    */
    private $rcLoginStatus;

    /**
    * Roundcube 0.5.1 adds a request token for 'security'. This variable
    * saves the last token and sends it with login and logout requests.
    *
    * @var string
    */
    private $lastToken;

    /**
    * Roundcube hostname. Automatically defaults to $_SERVER['HTTP_HOST'].
    *
    * @var string
    */
    private $hostname;

    /**
    * Roundcube port. Automatically defaults to 80/HTTP or 443/HTTPS.
    *
    * @var int
    */
    private $port;

    /**
    * Is SSL/TLS connection. Automatically defaults
    * to $_SERVER['HTTPS'] variable.
    *
    * @var boolean|null
    */
    private $ssl;

    /**
    * Отладку можно включить, задав второй аргумент в конструкторе TRUE.
    *
    * @var bool
    */
    private $debugEnabled;

    /**
    * Keep debug messages on a stack. To dump it, call
    * the dumpDebugStack()-function.
    *
    * @var array
    */
    private $debugStack;

    /**
    * Create a new RoundcubeLogin class.
    *
    * @param string Relative webserver path to the RC installation, e.g. /roundcube/
    * @param bool Enable debugging, - shows the full POST and the response
    */
    public function __construct($webmailPath, $enableDebug = TRUE)
    {
        // Переменные класса.
        $this->debugStack = array();
        $this->debugEnabled = $enableDebug;
        $this->rcPath = $webmailPath;
        $this->rcSessionID = false;
        $this->rcSessionAuth = false;
        $this->rcLoginStatus = 0;
        $this->hostname = false;
        $this->port = false;
        $this->ssl = null;
    }

    /**
    * Войдите в Roundcube, используя имя пользователя/пароль IMAP
    *
    * Примечание: если функция обнаруживает, что мы уже вошли в систему, она выполняет повторный вход,
    *             то есть комбинацию выхода/входа, чтобы гарантировать, что указанный пользователь вошел в систему.
    *
    *       Если вы этого не хотите, используйте функцию isLoggedIn() и перенаправьте
    *       RC без вузова login().
    *
    * @param string   имя пользователя IMAP
    * @param string   пароль IMAP (простой текст)
    * @return boolean Возвращает TRUE, если вход был успешным, в противном случае возвращает FALSE
    * @throws         RoundcubeLoginException
    */
    public function login($username, $password)
    {
        // Получаем текущий статус входа и файл cookie сеанса.
        $this->updateLoginStatus();
        // Если вы уже вошли в систему для выполнения повторного входа нужно выполнить выход из системы.
        if ($this->isLoggedIn()) {
            // Выход из системы.
            $this->logout();
        }
        // Пробуем войти: Формируем данные для POST - запроса.
        // В условии проверяем есть - ли токен: если нету - получаем.
        $data     = (($this->lastToken) ? "_token=" . $this->lastToken . "&" : "")
        // Выполняемая команда.
        . "_task=login&_action=login&_timezone=1&_dstactive=1&_url="
        // Логин и пароль.
        . "&_user=" . urlencode($username) . "&_pass=" . urlencode($password);
        // Переменной "response" присвоим ответ от сервера.
        $response = $this->sendRequest($this->rcPath, $data);
        // Авторизация успешна! Перенаправление на ./?_task = ... успешно.!
        if (preg_match('/^Location\:.+_task=/mi', $response)) {
            $this->addDebug("LOGIN SUCCESSFUL", "RC отправил перенаправление на ./?_task = ..., значит мы это сделали!");
            $this->rcLoginStatus = 1;
        }
        // Обнаружена ошибка входа! Если логин не удался, RC отправляет cookie "sessionsauth = - del - ".
        else
        if (preg_match('/^Set-Cookie:.+sessauth=-del-;/mi', $response)) {
            header($line, false);
            $this->addDebug("LOGIN FAILED", "RC sent 'sessauth=-del-'; User/Pass комбинация неправильная.");
            $this->rcLoginStatus = - 1;
        }
        // Неизвестно, ни неудач, ни успехов.
        // Это может быть так, если идентификатор сеанса не был отправлен.
        else {
            $this->addDebug("LOGIN STATUS UNKNOWN", "Нет ни неудач, ни успехов. Это может быть случай, если идентификатор сеанса не был отправлен" . "\r\n");
            // Отправим отладочное сообщение.
            throw new RoundcubeLoginException("Невозможно определить статус входа в систему из-за технических проблем." . "\r\n");
        }
        // Вернём значение функции "isLoggedIn()".
        return $this->isLoggedIn();
    }

    /**
    * Проверяем есть-ли активный сеанс Roundcube.
    *
    * @return bool Вернуть TRUE, если пользователь вошел в систему, иначе FALSE.
    * @throws      RoundcubeLoginException
    */
    public function isLoggedIn()
    {
        $this->updateLoginStatus();
        if (!$this->rcLoginStatus) {
            // Отправим отладочное сообщение.
            throw new RoundcubeLoginException("Невозможно определить статус входа в систему из-за технических проблем." . "\r\n");
        }
        // Если свойство"rcLoginStatus" больше 0 (true) - значит вошли в систему,
        // если меньше 0 (false) - значит не вошли в систему.
        return ($this->rcLoginStatus > 0) ? true : false;
    }

    /**
    * Получаем текущий статус входа и файл cookie сеанса.
    * Обновляем частные переменные rcSessionID и rcLoginStatus на
    * отправка запроса на главную страницу и анализ результата для формы входа.
    */
    private function updateLoginStatus($forceUpdate = false)
    {
        // Проверяем если свойства "rcSessionID" и "rcLoginStatus" объекта "rc" существуют:
        if ($this->rcSessionID && $this->rcLoginStatus && !$forceUpdate) {
            // То - просто вернёмся в вызывающую функцию.
            return;
        }
        // Если текущий сеанс активен - получаем файл cookie с идентификатором текущего сеанса.
        if ($_COOKIE['roundcube_sessid']) {
            $this->rcSessionID = $_COOKIE['roundcube_sessid'];
        }
        if ($_COOKIE['roundcube_sessauth']) {
            $this->rcSessionAuth = $_COOKIE['roundcube_sessauth'];
        }
        // Отправляем запрос на получение нового идентификатора сеанса и токена.
        $response = $this->sendRequest($this->rcPath);
        /**
        * preg_match — Выполняет проверку на соответствие регулярному выражению.
        * Описание:
        * preg_match (string $pattern, string $subject, array &$matches = null, int $flags = 0, int $offset = 0):int|false
        *
        * Ищет в заданном тексте subject совпадения с шаблоном pattern.
        *
        * @var pattern Искомый шаблон в виде строки.
        * @var subject Входная строка.
        * @var matches В случае, если указан дополнительный параметр matches, он будет заполнен результатами поиска.
        *              Элемент $matches[0] будет содержать часть строки, соответствующую вхождению всего шаблона,
        *              $matches[1] - часть строки, соответствующую первой подмаске и так далее.
        * @var flags   flags может быть комбинацией следующих флагов:
        *              PREG_OFFSET_CAPTURE В случае, если этот флаг указан, для каждой найденной подстроки
        *              будет указана её позиция (в байтах) в исходной строке. Необходимо помнить, что этот флаг меняет
        *              формат возвращаемого массива matches в массив, каждый элемент которого содержит массив,
        *              содержащий в индексе с номером 0 найденную подстроку, а смещение этой подстроки в параметре
        *              subject - в индексе 1.
        */
        // Далее в условиях проверяем ответ от сервера - получаем необходимые данные для авторизации:
        // Переопределим предыдущий токен (если он существует!)
        // Получим токен из заголовка ответа сервера (начиная с Roundcube 0.5.1).
        if (preg_match('/"request_token":"([^"]+)", /mi', $response, $m)) {
            // Запишем значение из массива "m[1]" в переменную "lastToken".
            $this->lastToken = $m[1];
            // Получим токен из страницы ответа сервера.
        } elseif (preg_match('/<input.+name="_token".+value="([^"]+)"/mi', $response, $m)) {
            // Запишем значение из массива "m[1]" в переменную "lastToken".
            $this->lastToken = $m[1];
        }
        // Если сервер прислал страницу с формой входа с полем для ввода пароля - значит мы не вошли в систему.
        if (preg_match('/<input.+name="_pass"/mi', $response)) {
            $this->addDebug("NOT LOGGED IN", "Обнаружено, что мы НЕ ВОШЛИ в систему.");
            $this->rcLoginStatus = - 1; // Не вошли в систему.
            // Если сервер прислал страницу со списком писем - значит сеанс активный.
        } elseif (preg_match('/<div.+id="messagetoolbar"/mi', $response)) {
            $this->addDebug("LOGGED IN", "Обнаружено, что мы ВОШЛИ в систему.");
            $this->rcLoginStatus = 1; // Вошли в систему.
            // Если нет доступного идентификатора сеанса, генерируем исключение.
        } else {
            $this->addDebug("NO SESSION ID", "Идентификатор сеанса не получен. Версия RC изменена?" . "\r\n");
            // Отправим отладочное сообщение.
            throw new RoundcubeLoginException("Идентификатор сеанса не получен. Невозможно продолжить из-за технических проблем." . "\r\n");
        }
        // Иначе невозможно определить статус входа.
        if (!$this->rcSessionID) {
            $this->addDebug("UNKNOWN LOGIN STATE", "Невозможно определить статус входа. Вы меняли версию RC?" . "\r\n");
            // Отправим отладочное сообщение.
            // Невозможно определить статус входа при активном сеансе.
            throw new RoundcubeLoginException("Невозможно определить статус входа. Невозможно продолжить из-за технических проблем." . "\r\n");
        }
    }

    /**
    * Отправьте запрос POST / GET сценарию входа в Roundcube
    * для имитации входа в систему.
    * Если установлен второй параметр $ postData, функция будет
    * используйте метод POST, иначе будет отправлено GET.
    * Обеспечивает отправку всех файлов cookie и анализирует все заголовки ответов
    * для нового идентификатора сеанса Roundcube. Если обнаружен новый SID, устанавливается rcSessionId.
    *
    * @param string  Необязательные данные POST в кодированной форме (param1=value1&...)
    * @return string Возвращает полный ответ на запрос со всеми заголовками.
    */
    private function sendRequest($path, $postData = false)
    {
        $method = (!$postData) ? "GET" : "POST";
        // Установить имя хоста и порт.
        $isSSL  = $this->ssl;
        $port   = intval($this->port);
        $host   = $this->hostname;

        if ($isSSL === null) {
            $isSSL = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'];
        }

        if (!$port) {
            if (isset($_SERVER['SERVER_PORT']) && is_numeric($_SERVER['SERVER_PORT'])) {
                $port = $_SERVER['SERVER_PORT'];
            }
            else
            if ($isSSL) {
                $port = 443;
            }
            else {
                $port = 80;
            }
        }

        if (!$host) {
            if ($isSSL && isset($_SERVER['SSL_TLS_SNI']) && $_SERVER['SSL_TLS_SNI']) {
                $host = $_SERVER['SSL_TLS_SNI'];
            }
            else
            if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']) {
                $host = $_SERVER['HTTP_HOST'];
            }
            else {
                $host = "127.0.0.1";
            }
        }
        // Если SSL существует - присвоим хосту.
        if ($isSSL) $host    = "ssl://$host";
        // Инициируем переменную - массив "cookies".
        $cookies = array();
        // Если глобальный массив "$_COOKIE" существует.
        if ($_COOKIE) {
            // В цикле перебираем глобальный массив "_COOKIE", получаем имя и значение куков.
            // Загрузите файлы cookie и сохраните их в массиве ключ / значение.
            foreach ($_COOKIE as $name=>$value) {
                $cookies[] = "$name=$value";
            }
            // Если доступен идентификатор сеанса roundcube - добавим его.
            if (!$_COOKIE['roundcube_sessid'] && $this->rcSessionID) $cookies[] = "roundcube_sessid={$this->rcSessionID}";
            if (!$_COOKIE['roundcube_sessauth'] && $this->rcSessionAuth) $cookies[] = "roundcube_sessauth={$this->rcSessionAuth}";
        }
        // Перезапишем переменную "cookies" (в первом запросе к серверу отправляем пустые куки).
        $cookies = ($cookies) ? "Cookie: ".join("; ", $cookies)."\r\n" : "";
        // Создаём запрос с куками для получения идентификатора сесси.
        // Создать запрос POST с заданными данными.
        if ($method == "POST") {
            $request =
            "POST ".$path." HTTP/1.1\r\n"
            . "Host: ".$_SERVER['HTTP_HOST']."\r\n"
            . "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n"
            . "Content-Type: application/x-www-form-urlencoded\r\n"
            . "Content-Length: ". strlen($postData) ."\r\n"
            . $cookies
            . "Connection: close\r\n\r\n"
            . $postData;
        }
        // Создать запрос GET с заданными данными.
        else {
            $request =
            "GET ".$path." HTTP/1.1\r\n"
            . "Host: ".$_SERVER['HTTP_HOST']."\r\n"
            . "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n"
            . $cookies
            . "Connection: close\r\n\r\n";
        }
        /**
        * fsockopen — Открывает соединение с интернет-сокетом или доменным сокетом Unix.
        * Описание
        * fsockopen (string $hostname, int $port = -1, int &$error_code = null, string &$error_message = null, float|null $timeout = null):resource|false
        *
        * Устанавливает соединение с сокетом ресурса hostname.
        * PHP поддерживает целевые ресурсы в интернете и Unix-доменах в том виде, как они описаны в списке поддерживаемых транспортных протоколов.
        * Список поддерживаемых транспортов можно получить с помощью функции stream_get_transports().
        * По умолчанию сокет будет открыт в блокирующем режиме. Переключить его в неблокирующих режим можно функцией stream_set_blocking().
        * stream_socket_client() выполняет аналогичную функцию, но предоставляет более широкий выбор настроек соединения,
        * включающий установку неблокирующего режима и возможность предоставления потокового контекста.
        *
        * @var hostname      Если установлена поддержка OpenSSL, можно использовать SSL- или TLS-протоколы соединений
        *                    поверх TCP/IP при подключении к удалённому хосту. Для этого перед hostname нужно добавить префикс ssl:// или tls://.
        * @var port          Номер порта. Его можно не указывать, передав -1 для тех протоколов, которые не используют порты, например unix://.
        * @var error_code    Если этот параметр предоставить, то в случае ошибки системного вызова функции connect() он будет принимать номер этой ошибки.
        *                    Если значение параметра error_code равно 0, а функция вернула false, значит ошибка произошла до вызова connect().
        *                    В большинстве случаев это свидетельствует о проблемах при инициализации сокета.
        * @var error_message Сообщение об ошибке в виде строки.
        * @var timeout       Тайм-аут соединения в секундах.
        *
        * Замечание:
        * Если требуется установить тайм-аут чтения/записи данных через сокет, используйте функцию stream_set_timeout(),
        * т.к. параметр timeout функции fsockopen() ограничивает только время процесса установки соединения с сокетом.
        *
        * Возвращаемые значения:
        * fsockopen() возвращает файловый указатель, который можно использовать с функциями,
        * работающие с файлами (такие как fgets(), fgetss(), fwrite(), fclose() и feof()). Если вызов завершится неудачно, функция вернёт false.
        *
        * Ошибки:
        * Вызывает ошибку уровня E_WARNING, если hostname не является допустимым доменом.
        */
        // Оправляем запрос на сервер.
        $fp = fsockopen($host, $port);
        // Условие проверки существования переменной "fp", если её нет - значит запрос не отправился.
        if (!$fp) {
            $this->addDebug("UNABLE TO OPEN SOCKET", "Не удалось открыть сокет для $host в порту $port");
            // Отправим отладочное сообщение.
            throw new RoundcubeLoginException("Не удалось открыть сокет для $host в порту $port");
        }
        // Отладочное сообщение содержащее наш запрос.
        $this->addDebug("REQUEST", $request);
        // fputs или fwrite — Бинарно - безопасная запись в файл.
        // Читаем ответ от сервера через переменную "fp".
        fputs($fp, $request);
        // Инициализируем переменную "response".
        $response = "";
        // Функция feof — проверяет, достигнут ли конец файла (выполняется долго).
        // Читаем ответ от сервера - присланная страница ответа в браузер.
        // Читаем содержимое переменной "fp" и формируем страницу ответа от вэб - сервера (страница входа).
        // Прочитаем ответ от сервера и установим полученные куки.
        while (!feof($fp)) {
            /**
            * Функция fgets — читает строку из файла
            * Описание:
            * fgets(resource $handle, int $length = ?):string
            * Читает строку из файлового указателя.
            * Список параметров:
            * @var  handle Указатель на файл должен быть корректным и указывать на файл,
            *              успешно открытый функциями fopen() или fsockopen() (и всё ещё не закрытый функцией fclose()).
            * @var length  Чтение заканчивается при достижении length-1 байт, (это длина считываемой строки строки в байтах)
            *              либо если встретилась новая строка (которая включается в возвращаемый результат)
            *              или конец файла (в зависимости от того, что наступит раньше). Если длина не указана,
            *              чтение из потока будет продолжаться до тех пор, пока не достигнет конца строки.
            * Возвращаемые значения:
            * Возвращает строку размером в length - 1 байт, прочитанную из дескриптора файла,
            * на который указывает параметр handle. Если данных для чтения больше нет, то возвращает false.
            * В случае возникновения ошибки возвращает false.
            */
            $line = fgets($fp, 4096);
            // В условиях проверяем полученные строки и с помощбю регулярных выражений получаем необходимые данные.
            if (preg_match('/^HTTP\/1\.\d\s+404\s+/', $line)) {
                // Отправим отладочное сообщение.
                throw new RoundcubeLoginException("Установка Roundcube не найдена на '$path'");
                //}
                // Получаем идентификатор сеанса.
            } elseif (preg_match('/^Set-Cookie:\s*(.+roundcube_sessid=([^;]+);.+)$/i', $line, $match)) {
                // Отправим серверу HTTP - заголовок с имеющимися куками.
                header($line, false);
                // Отправим отладочное сообщение.
                $this->addDebug("GOT SESSION ID", "Новая сессия ID: '$match[2]'.");
                // Присвоим ID новой сессии согласно имеющимися куками.
                $this->rcSessionID = $match[2];
                //}
                // Получил сессию.
            } elseif (preg_match('/^Set-Cookie:.+roundcube_sessauth=([^;]+);/i', $line, $match)) {
                header($line, false);// Отправим серверу HTTP - заголовок с куками.
                // Отправим отладочное сообщение.
                $this->addDebug("GOT SESSION AUTH", "Авторизация нового сеанса: '$match[1]'.");
                // Присвоим полученный от сервера "rcSessionAuthi".
                $this->rcSessionAuthi = $match[1];
                //}
                // Получаем токен запроса (начиная с Roundcube 0.5.1).
            } elseif (preg_match('/"request_token":"([^"]+)", /mi', $response, $m)) {
                // Присвоим переменной "lastToken" полученный от сервера токен.
                $this->lastToken = $m[1];
                //}
                // Получаем токен из формы ввода логина и пароля.
            } elseif (preg_match('/<input.+name="_token".+value="([^"]+)"/mi', $response, $m)) {
                // Переопределим предыдущий токен (если он существует!), запишем в массив "m".
                $this->lastToken = $m[1];
            }
            $response .= $line;
        }
        // Функция fclose — закрывает открытый дескриптор файла.
        fclose($fp);
        // Вызываем функцию addDebug().
        $this->addDebug("RESPONSE", $response);
        return $response;
    }

    /**
    * Выход из Roundcube.
    * @return bool Возвращает TRUE, если вход был успешным, в противном случае возвращает FALSE.
    */
    public function logout()
    {
        $data = (($this->lastToken) ? "_token=".$this->lastToken."&" : "") . "_action=logout&_task=logout";
        $this->sendRequest($this->rcPath, $data);
        return !$this->isLoggedIn();
    }

    /**
    * Перенаправление в приложение Roundcube.
    */
    public function redirect($folder, $sub_folder)
    {
        // Перенаправление на главную страницу.
        //header("Location: {$this->rcPath}");
        // Перенаправление на нужную страницу.
        $rcPath = $this->rcPath . "?_task=mail&_mbox=INBOX%2FArchive%2F" . $folder . "%2F" . $sub_folder;
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
        * Location: http://www.faqs.org/rfcs/rfc2616.html
        */
        header("Location: {$rcPath}");
        // И выход из программы.
        //exit;

    }

    /**
    * Set hostname manually. Note that the hostname
    * must point to the local machine. It does not work
    * for remote machines.
    * @param string The hostname, or FALSE to use default
    */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

    /**
    * Set port manually. Uses 80/443 by default.
    * @param int The port, or FALSE to use default.
    */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
    * Enable or disable SSL for this connection. This value
    * impacts the connection string for fsockopen(). If enabled,
    * the prefix "ssl://" is attached. If NULL is set, the value of
    * the $_SERVER['HTTPS'] variable is used.
    * @param boolean|null Set TRUE to enable, FALSE to disable, NULL to auto-detect
    */
    public function setSSL($enableSSL)
    {
        $this->ssl = $enableSSL;
    }

    /**
    * Распечатайте отладочное сообщение, если отладка включена.
    *
    * @param string Короткое сообщение о действии
    * @param string Выходные данные
    */
    private function addDebug($action, $data)
    {
        if (!$this->debugEnabled) return false;
        $this->debugStack[] = sprintf(
            "<b>%s:</b><br /><pre>%s</pre>",
            $action, htmlspecialchars($data)
        );
    }

    /**
    * Дамп стека отладки.
    */
    public function dumpDebugStack()
    {
        print "<p>".join("\n", $this->debugStack)."</p>";
    }
}

/**
* Это исключение входа в систему Roundcube будет сгенерировано, если две попытки входа не удастся.
*/
class RoundcubeLoginException extends Exception
{
}
// End of class RoundcubeLogin