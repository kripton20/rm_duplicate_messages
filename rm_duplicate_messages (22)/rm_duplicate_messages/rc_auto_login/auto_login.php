<!DOCTYPE html>
<html lang="ru">
    <head>
        <title>
            Усовершенствованный скрипт блокировки сервера
        </title>
        <meta charset='utf-8'>
    </head>
    <body>
        <!-- I-вариант.
        <?php
        if (!isset($_REQUEST['doGo'])) {
        ?>
        <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="post" name="autologin">
        E-mail: <input type="text" name="rc_user" id="rc_user" value=""><br /><br/>
        Password: <input type="password" name="rc_password" id="rc_password" value=""><br /><br/>
        <input type="submit" name="doGo" value="Авторизация">
        </form>
        <?php
        } ?>-->
        <!--        // II-вариант.-->
        <?php
        if (!isset($_REQUEST['doGo'])) {
            ?>
            <form action="<?=$_SERVER['SCRIPT_NAME']?>" method="post" name="autologin">
                E-mail: <input type="text" name="rc_user" id="rc_user" value=""><br /><br/>
                Password: <input type="password" name="rc_password" id="rc_password" value=""><br /><br/>
                <input type="submit" name="doGo" value="Авторизация">
            </form>
            <?php
        } else {
            require_once(__DIR__ . '/roundcube_auto_login.php');
            if ($_REQUEST['rc_user'] == "root" && $_REQUEST['rc_password'] == "Z10N0101") {
                if ($_REQUEST['rc_user'] == "l51@niiemp.local" && $_REQUEST['rc_password'] == "l51v6249niiemp") {
                    echo "Доступ открыт для пользователя {$_REQUEST['rc_user']} <br/>";
                    echo $_SERVER['SCRIPT_NAME'] . "<br/>";
                    // Команда блокирования рабочей станции (работает в NT - системах)
                    //system("rundll32.exe user32.dll,LockWorkStation");
                    require_once(__DIR__ . '/roundcube_auto_login.php');
                } else {
                    echo "<br/>Доступ закрыт!<br/>";
                }
            }
        } ?>
    </body>
</html>
