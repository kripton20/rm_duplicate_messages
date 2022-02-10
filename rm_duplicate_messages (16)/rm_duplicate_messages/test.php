<?php
// Логический операатор
// РНР так же, как и любой С-подобный язык, предоставляет условный оператор, который
// возвращает y, в случае если x принимает значение true, и z в случае, если x принимает
// значение false
// x ? y : z

// http://localhost/rc147/plugins/rm_duplicate_messages/test.php
//$source = 'text_text2_text3';
//$source = '=?UTF-8?B?0J7QntCeINCc0JLQoQ==?= <ooomws@bk.ru>';
//
//$offset = strpos($source, '<');
//$result = ($offset) ? substr($source,0,$offset) : $source;
// 
//echo "{$source} => {$result}";
//
//
//
//$source = '=?UTF-8?B?0J7QntCeINCc0JLQoQ==?= <ooomws@bk.ru>';
//$result = explode('<', $source, 1);
//echo "<pre>";
//print_r($result);
//echo "</pre>";

// Пример 2
$data = '=?UTF-8?B?0J7QntCeINCc0JLQoQ==?= <ooomws@bk.ru>';
list($a, $data1) = explode('<', $data);
list($data2, $b) = explode('>', $data1);

		        	// Вызов автоматической авторизации:
        	// Загрузка класса:
// вместо указания абсолютного пути используем предопределенную константу __DIR__,
// сообщающую текущий каталог скрипта.
$d = __DIR__;
//require_once('/rc_auto_login/roundcube_auto_login.php');
//Это работает
include_once(__DIR__ . '/rm_duplicate_messages/rc_auto_login/roundcube_auto_login.php');
require_once(__DIR__ . '/rc_auto_login/roundcube_auto_login.php');



$d=__DIR__;
$f = __FILE__;





?>