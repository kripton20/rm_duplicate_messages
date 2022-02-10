<?php
// 1. создать ассоциативный массив (это понятно)
 
$array = array();
 
//2. в новый ассоциативный массив передать первый элемент типа ключ=>значение (это тоже понятно)
 
$key = 'key1';
$value = 'value1';
 
$array[$key] = $value;
 
//3. далее программно необходимо пополнять ассоциативный массив новыми элементами (ключ=>значение);
 
$key = array ('mkey1','mkey2','mkey3','mkey4');
$value = array ('mvalue1', 'mvalue2', 'mvalue3', 'mvalue4');
 
for ($i = 0; $i<count($key); $i++) {
    $array[$key[$i]] = $value[$i];
}
 
print_r ($array);
?>