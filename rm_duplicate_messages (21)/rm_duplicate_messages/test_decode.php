<?php
$subject = '=?utf-8?B?0JDQvdC60LXRgtCwINC+0YbQtdC90LrQuCDRg9C00L7QstC70LXRgtCy0L7RgA==?= =?utf-8?B?0LXQvdC40Y8g0L/QvtGC0YDQtdCx0LjRgtC10LvRjw==?=';
//list($a, $data1) = explode('=?utf-8?B?', $data);
//list($data2, $b) = explode('>', $data1);

// =?utf-8?B?0JPRgNGD0L/Qv9CwINC60L7QvNC/0LDQvdC40LkgItCU0LXQu9C+0LLRi9C1IA==?=
// =?utf-8?B?0JvQuNC90LjQuCIg0KHRh9C10YIoLdCwKSDQvdCwINC+0L/Qu9Cw0YLRgw==?=
// =?utf-8?B?0JPRgNGD0L/Qv9CwINC60L7QvNC/0LDQvdC40LkgItCU0LXQu9C+0LI=?=
// =?utf-8?B?0YvQtSDQm9C40L3QuNC4IiDQodGH0LXRgigt0LApINC90LAg0L7Qv9C70LDRgtGD?=
$v1 = "=?utf-8?B?0YvQtSDQm9C40L3QuNC4IiDQodGH0LXRgigt0LApINC90LAg0L7Qv9C70LDRgtGD?=";

$substr       = substr($v1, 10, strlen($v1));
$substr1 = substr($substr, strpos($substr, '?='));
$substr2=trim($substr, "?=");
$substr3=strstr($substr, '?=', true);

//$substr=explode('?=', $substr);
// Обрезка текста до первого пробела
//$text = 'Hello world!';
//echo substr($text, 0, strpos($text, ' ' )); // Выведет 'Hello'
$substr1 = substr($v1, strlen($v1), strpos($v1, '=' ));

// Разделим по пробелу.
$subject1 = explode(" ", $subject);

// Циклом пройдем по массиву.
foreach ($subject1 as $value => $subject_data) {
	$a=1;
}

$str = '0K3RgtC+INC30LDQutC+0LTQuNGA0L7QstCw0L3QvdCw0Y8g0YHRgtGA0L7QutCw';
echo base64_decode($str);
echo "<br />";



$str1 = '0JPRgNGD0L/Qv9CwINC60L7QvNC/0LDQvdC40LkgItCU0LXQu9C+0LLRi9C1IA';
$str2 = '0JvQuNC90LjQuCIg0KHRh9C10YIoLdCwKSDQvdCwINC+0L/Qu9Cw0YLRgw';
$str3 = '0JPRgNGD0L/Qv9CwINC60L7QvNC/0LDQvdC40LkgItCU0LXQu9C+0LI';
$str4 = '0YvQtSDQm9C40L3QuNC4IiDQodGH0LXRgigt0LApINC90LAg0L7Qv9C70LDRgtGD';

echo "1 " . base64_decode($str1) . "<br />";
echo "2 " . base64_decode($str2) . "<br />";
echo "3 " . base64_decode($str3) . "<br />";
echo "4 " . base64_decode($str4) . "<br />";

?>