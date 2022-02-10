<?php
/**
* Обновление страницы в приложении Roundcube.
*/
$host       = $_GET['host'];
$folder     = $_GET['folder'];
$sub_folder = $_GET['sub_folder'];
// Перенаправление на главную страницу.
//header("Location: {$this->rcPath}");
// Перенаправление на нужную страницу.
//$rcPath = $this->rcPath . "?_task = mail & _mbox = INBOX % 2FArchive % 2F" . $folder . " % 2F" . $sub_folder;
$rcPath     = $host . "?_task=mail&_mbox=INBOX%2FArchive%2F" . $folder . "%2F" . $sub_folder;
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
// И выход из программы.
exit;
?>