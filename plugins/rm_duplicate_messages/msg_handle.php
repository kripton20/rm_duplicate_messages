<?php
// В условии проверяем существование параметров.
if ($_GET['amount'] || $_GET['apr'] || $_GET['years'] || $_GET['zipcode'] || $_GET['uids']) {
    // Переменным присвоим переданные значения.
    $amount = $_GET['amount'];
    $apr    = $_GET['apr'];
    $years  = $_GET['years'];
    $zipcode= $_GET['zipcode'];
    $uids=$_GET['uids'];
}

// Создаём одномерный массив.
$data = array(
    // Массив первого уровня.
    array(
        'amount' => $_GET['amount'],
        'apr'    => $_GET['apr'],
        'years'  => $_GET['years'],
        'zipcode'=> $_GET['zipcode']
    )
    // Массив второго уровня.
    //array('name' => 'bar', 'url' => 'url2'),
);
echo('Привет');
?>