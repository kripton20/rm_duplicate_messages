<?php

/**
* Use a PHP script to perform a login to the Roundcube mail system.
*
* SCRIPT VERSION
*   Version 4 (July 2013)
*
* DOWNLOAD & DOCUMENTATION AT
*   http://blog.philippheckel.com/2008/05/16/roundcube-login-via-php-script/
*
* REQUIREMENTS
*   - A Roundcube installation (tested with 0.9.2)
*    (older versions work with 0.2-beta, 0.3.x, 0.4-beta, 0.5, 0.5.1, 0.7.2)
*
*   - Set the "check_ip"/"ip_check" in the config/main.inc.php file to FALSE
*     Why? The server will perform the login, not the client (= two different IP addresses)
*
* INSTALLATION
*   - Install RC on your server so that it can be accessed via the browser,
*     e.g. at www.example.com/roundcube/
*
*   - Download this script and remove all spaces and new lines
*     before "<?php" and after "?>"
*
*   - Include the class in your very own script and use it.
*
* USAGE
*   The class provides four public methods:
*
*   - login($username, $password)
*         Perform a login to the Roundcube mail system.
*
*         Note: If the client is already logged in, the script will re-login the user (logout/login).
*               To prevent this behaviour, use the isLoggedIn()-function.
*
*         Returns: TRUE if the login suceeds, FALSE if the user/pass-combination is wrong
*         Throws:  May throw a RoundcubeLoginException if Roundcube sends an unexpected answer
*                  (that might happen if a new Roundcube version behaves different).
*
*   - isLoggedIn()
*         Checks whether the client/browser is logged in and has a valid Roundcube session.
*
*         Returns: TRUE if the user is logged in, FALSE otherwise.
*         Throws:  May also throw a RoundcubeLoginException (see above).
*
*   - logout()
*         Performs a logout on the current Roundcube session.
*
*         Returns: TRUE if the logout was a success, FALSE otherwise.
*         Throws:  May also throw a RoundcubeLoginException (see above).
*
*   - redirect()
*         Simply redirects to Roundcube.
*
*   - setHostname($hostname)
*       Override default hostname. Only a local host is possible.
*
*   - setPort($port)
*       Override automatic port detection.
*
*   - setSSL($enableSSL)
*       Override automatic SSL detection.
*
* SAMPLE CODE
*   <?php
*
*       include "RoundcubeLogin.class.php";
*
*       // Create login object and enable debugging
*       $rcl = new RoundcubeLogin("/roundcube/", true);
*
*       try {
*           // If we are already logged in, simply redirect
*           if ($rcl->isLoggedIn())
*               $rcl->redirect();
*
*           // If not, try to login and simply redirect on success
*           $rcl->login("your-email-address", "plain-text-password");
*
*           if ($rcl->isLoggedIn())
*               $rcl->redirect();
*
*           // If the login fails, display an error message
*           die("ERROR: Login failed due to a wrong user/pass combination.");
*       }
*       catch (RoundcubeLoginException $ex) {
*           echo "ERROR: Technical problem, ".$ex->getMessage();
*           $rcl->dumpDebugStack(); exit;
*       }
*
*   ?>
*
* TROUBLESHOOTING
*   - Make sure to remove all spaces before "<?php" and after "?>"
*   - Enable the debug mode (set the second constructor parameter to TRUE)
*   - Ask me if you have any problems :-)
*
* AUTHOR/LICENSE/VERSION
*   - Written by Philipp Heckel; Find a corresponding blog-post at
*     http://blog.philippheckel.com/2008/05/16/roundcube-login-via-php-script/
*
*   - Updated July 2013, tested with Ubuntu/Firefox 3
*     No license. Feel free to use it :-)
*
*   - The updated script has been tested with Roundcube 0.9.2.
*     Older versions of the script work with Roundcube 0.2, 0.3, 0.4-beta,
*     0.5.1 and 0.7.2 (see blog post above)
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
    * Roundcube hostname. Automatically defaults
    * to $_SERVER['HTTP_HOST'].
    *
    * @var string
    */
    private $hostname;

    /**
    * Roundcube port. Automatically defaults
    * to 80/HTTP or 443/HTTPS.
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
    * Debugging can be enabled by setting the second argument
    * in the constructor to TRUE.
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
    public function __construct($webmailPath, $enableDebug = false)
    {
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
    * @param string имя пользователя IMAP
    * @param string пароль IMAP (простой текст)
    * @return boolean Возвращает TRUE, если вход был успешным, в противном случае возвращает FALSE
    * @throws RoundcubeLoginException
    */
    public function login($username, $password)
    {
        $this->updateLoginStatus();

        // Если вы уже вошли в систему, выполните повторный вход (сначала выйдите из системы).
        if ($this->isLoggedIn())
        $this->logout();

        // Попробуй войти.
        $data     = (($this->lastToken) ? "_token=".$this->lastToken."&" : "")
        . "_task=login&_action=login&_timezone=1&_dstactive=1&_url=&_user=".urlencode($username)."&_pass=".urlencode($password);

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

            $this->addDebug("LOGIN FAILED", "RC sent 'sessauth=-del-'; User/Pass combination wrong.");
            $this->rcLoginStatus = - 1;
        }

        // Неизвестно, ни неудач, ни успехов.
        // Это может быть так, если идентификатор сеанса не был отправлен.
        else {
            $this->addDebug("LOGIN STATUS UNKNOWN", "Ни неудач, ни успехов. Это может быть случай, если идентификатор сеанса не был отправлен");
            throw new RoundcubeLoginException("Невозможно определить статус входа в систему из-за технических проблем.");
        }

        return $this->isLoggedIn();
    }

    /**
    * Возвращает, есть ли активный сеанс Roundcube.
    *
    * @return bool Вернуть TRUE, если пользователь вошел в систему, иначе FALSE.
    * @throws RoundcubeLoginException
    */
    public function isLoggedIn()
    {
        $this->updateLoginStatus();

        if (!$this->rcLoginStatus)
        throw new RoundcubeLoginException("Невозможно определить статус входа в систему из-за технических проблем.<br />");

        return ($this->rcLoginStatus > 0) ? true : false;
    }

    /**
    * Logout from Roundcube
    * @return bool Returns TRUE if the login was successful, FALSE otherwise
    */
    public function logout()
    {
        $data = (($this->lastToken) ? "_token=".$this->lastToken."&" : "")
        . "_action=logout&_task=logout";

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
        //$rcPath = $this->rcPath . "?_task=mail&_mbox=INBOX%2FArchive%2FInbox2020%2F2020_1";
        $rcPath = $this->rcPath . "?_task=mail&_mbox=INBOX%2FArchive%2F" . $folder . "%2F" . $sub_folder;
        header("Location: {$rcPath}");
        // И выход.
        exit;
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
    * Получает текущий статус входа и файл cookie сеанса.
    *
    * Он обновляет частные переменные rcSessionID и rcLoginStatus на
    * отправка запроса на главную страницу и анализ результата для формы входа.
    */
    private function updateLoginStatus($forceUpdate = false)
    {
        if ($this->rcSessionID && $this->rcLoginStatus && !$forceUpdate)
        return;

        // Получить файл cookie с идентификатором текущего сеанса
        if ($_COOKIE['roundcube_sessid'])
        $this->rcSessionID = $_COOKIE['roundcube_sessid'];

        if ($_COOKIE['roundcube_sessauth'])
        $this->rcSessionAuth = $_COOKIE['roundcube_sessauth'];

        // Отправить запрос и, возможно, получить новый идентификатор сеанса.
        $response = $this->sendRequest($this->rcPath);

        // Токен запроса (начиная с Roundcube 0.5.1).
        if (preg_match('/"request_token":"([^"]+)", /mi', $response, $m))
        $this->lastToken = $m[1];

        if (preg_match('/<input.+name="_token".+value="([^"]+)"/mi', $response, $m))
        $this->lastToken = $m[1]; // override previous token (if this one exists!)

        // Доступна форма входа?
        if (preg_match('/<input.+name="_pass"/mi', $response)) {
            $this->addDebug("NOT LOGGED IN", "Обнаружено, что мы НЕ вошли в систему.");
            $this->rcLoginStatus = - 1;
        }

        else
        if (preg_match('/<div.+id="message"/mi', $response)) {
            $this->addDebug("LOGGED IN", "Detected that we're logged in.");
            $this->rcLoginStatus = 1;
        }

        else {
            $this->addDebug("UNKNOWN LOGIN STATE", "Невозможно определить статус входа. Вы меняли версию RC?");
            throw new RoundcubeLoginException("Невозможно определить статус входа. Невозможно продолжить из-за технических проблем.");
        }

        // Если сейчас нет доступного идентификатора сеанса, генерировать исключение.
        if (!$this->rcSessionID) {
            $this->addDebug("NO SESSION ID", "Идентификатор сеанса не получен. Версия RC изменена?");
            throw new RoundcubeLoginException("Идентификатор сеанса не получен. Невозможно продолжить из-за технических проблем.");
        }
    }

    /**
    * Отправьте запрос POST / GET сценарию входа в Roundcube
    * для имитации входа в систему.
    *
    * Если установлен второй параметр $ postData, функция будет
    * используйте метод POST, иначе будет отправлено GET.
    *
    * Обеспечивает отправку всех файлов cookie и анализирует все заголовки ответов
    * для нового идентификатора сеанса Roundcube. Если обнаружен новый SID, устанавливается rcSessionId.
    *
    * @param string Необязательные данные POST в кодированной форме (param1=value1&...)
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

        if ($isSSL) {
            $host = "ssl://$host";
        }

        // Загрузите файлы cookie и сохраните их в массиве ключ / значение.
        $cookies = array();

        foreach ($_COOKIE as $name=>$value) {
            $cookies[] = "$name=$value";
        }

        // Добавьте идентификатор сеанса roundcube, если он доступен.
        if (!$_COOKIE['roundcube_sessid'] && $this->rcSessionID)
        $cookies[] = "roundcube_sessid={$this->rcSessionID}";

        if (!$_COOKIE['roundcube_sessauth'] && $this->rcSessionAuth)
        $cookies[] = "roundcube_sessauth={$this->rcSessionAuth}";

        $cookies = ($cookies) ? "Cookie: ".join("; ", $cookies)."\r\n" : "";

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

        // Сделать GET.
        else {
            $request =
            "GET ".$path." HTTP/1.1\r\n"
            . "Host: ".$_SERVER['HTTP_HOST']."\r\n"
            . "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n"
            . $cookies
            . "Connection: close\r\n\r\n";
        }

        // fsockopen — Открывает соединение с интернет - сокетом или доменным сокетом Unix
        // Оправим запрос.
        $fp = fsockopen($host, $port);

        if (!$fp) {
            $this->addDebug("UNABLE TO OPEN SOCKET", "Не удалось открыть сокет для $host в порту $port");
            throw new RoundcubeLoginException("Не удалось открыть сокет для $host в порту $port");
        }

        // Отладочное сообщение от запрос.
        $this->addDebug("REQUEST", $request);
        // Функция fputs — псевдоним фунции fwrite()
        // Функция fwrite — Бинарно - безопасная запись в файл.
        fputs($fp, $request);

        // Прочитать ответ и установить полученные куки
        $response = "";

        // Функция feof — проверяет, достигнут ли конец файла (выполняется долго).
        // Читаем содержимое переменной "fp" и формируем страницу ответа от вэб - сервера (страница входа).
        // Эта часть выполняется очень долго.
        while (!feof($fp)) {
            // fgets — Читает строку из файла
            $line = fgets($fp, 4096);

            // Не найден.
            if (preg_match('/^HTTP\/1\.\d\s+404\s+/', $line))
            throw new RoundcubeLoginException("Установка Roundcube не найдена на '$path'");

            // Получил идентификатор сеанса!
            if (preg_match('/^Set-Cookie:\s*(.+roundcube_sessid=([^;]+);.+)$/i', $line, $match)) {
                header($line, false);

                $this->addDebug("GOT SESSION ID", "Новая сессия ID: '$match[2]'.");
                $this->rcSessionID = $match[2];
            }

            // Получил сессию.
            if (preg_match('/^Set-Cookie:.+roundcube_sessauth=([^;]+);/i', $line, $match)) {
                header($line, false);

                $this->addDebug("GOT SESSION AUTH", "Авторизация нового сеанса: '$match[1]'.");
                $this->rcSessionAuthi = $match[1];
            }

            // Токен запроса (начиная с Roundcube 0.5.1).
            if (preg_match('/"request_token":"([^"]+)", /mi', $response, $m))
            $this->lastToken = $m[1];

            if (preg_match('/<input.+name="_token".+value="([^"]+)"/mi', $response, $m))
            // Переопределим предыдущий токен (если он существует!).
            $this->lastToken = $m[1];

            $response .= $line;
        }
        // Функция fclose — закрывает открытый дескриптор файла.
        fclose($fp);
        // Вызываем функцию addDebug().
        $this->addDebug("RESPONSE", $response);
        return $response;
    }

    /**
    * Распечатайте отладочное сообщение, если отладка включена.
    *
    * @param string Короткое сообщение о действии
    * @param string Выходные данные
    */
    private function addDebug($action, $data)
    {
        if (!$this->debugEnabled)
        return false;

        $this->debugStack[] = sprintf(
            "<b>%s:</b><br /><pre>%s</pre>",
            $action, htmlspecialchars($data)
        );
    }

    /**
    * Dump the debug stack
    */
    public function dumpDebugStack()
    {
        print "<p>".join("\n", $this->debugStack)."</p>";
    }
}

/**
* This Roundcube login exception will be thrown if the two
* login attempts fail.
*/
class RoundcubeLoginException extends Exception
{
}

// End of class RoundcubeLogin