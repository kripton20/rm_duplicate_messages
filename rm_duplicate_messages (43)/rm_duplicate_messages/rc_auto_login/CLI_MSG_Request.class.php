<?php
//Р асширяем наш класс от класса RoundcubeLogin.
class Parsing extends RoundcubeLogin
{
	/**
    * Относительный путь к базовому каталогу Roundcube на сервере.
    *
    * Можно установить через первый аргумент в конструкторе.
    * Если URL-адрес www.example.com/roundcube/, установите его как «/roundcube/».
    *
    * @var string
    */
    //private $folder;
    //private $sub_folder;
    
    /**
    * Перенаправление в приложение Roundcube.
    */
    public function redirect_in_folder($folder, $sub_folder)
    {
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
        // Перенаправление на страницу указанную в переменной "rcPath".
        header("Location: {$rcPath}");
        // И выход из программы.
        //exit;
    }

}
?>