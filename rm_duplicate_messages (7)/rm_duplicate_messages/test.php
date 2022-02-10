<?php
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

?>