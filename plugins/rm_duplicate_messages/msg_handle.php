<?php
// В условии проверяем существование параметров.
if ($_GET['amount'] || $_GET['apr'] || $_GET['years'] || $_GET['zipcode'] || $_GET['uids']) {
	// Переменным присвоим переданные значения.
	$amount = $_GET['amount'];
	$apr    = $_GET['apr'];
	$years  = $_GET['years'];
	$zipcode= $_GET['zipcode'];
	$uids   = $_GET['uids'];
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

//		/**
// * Инициализация и получение объекта хранения писем.
// * @return rcube_storage   Объект хранения (Storage)
//		*/
//		$Storage = $this->rc->get_storage();
//
//        /**
// * Цикл получения заголовков сообщения по текущему 'uid'.
// * Циклом foreach перебираем вложенный массив '$uids[$folder]' и получаем 'uid'
// * каждого отдельного сообщения, присвоим это значение переменной '$msg_uid'.
//        */
//        foreach ($uids[$folder] as $msg_uid) {
//            // Разбираем сообщение. Начало
//            /**
// * Получение заголовков сообщений и структуры тела с сервера и построение структуры объекта,
// * подобной той, которая создается PEAR::Mail_mimeDecode
// *
// * get_message (int $uid, string $folder = null): object
// *
// * @param int $uid        UID сообщения для получения
// * @param string    $folder Папка для чтения
// *
// * @return object rcube_message_header Данные сообщения
//            */
//            // получаем заголовки сообщения
//            $msg_headers = $Storage->get_message($msg_uid, $folder);
//
//            // если сообщение имеет флаг 'DUBLIKAT' - пропустим это сообщение (начнём новую интерацию текущего цикла)
//            if (isset($msg_headers->flags['DUBLIKAT'])) {
//                // увеличим счётчики первого и второго сообщения и повторяем весь цикл
//                //$msg_offset++;
//                //$msg2_offset = $msg_offset + 1;
//                // очищаем массивы и переменные первого и второго сообщения, функция unset()
//                //unset($msg_headers, $msg_uid);
//                // начнём цикл заново
//                continue;
//            }
//
//            /**
// * Получаем тело определенного сообщения с сервера
// *
// * get_message_part(int $uid, string $part = 1, \rcube_message_part $o_part = null, mixed $print = null, resource $fp = null, boolean $skip_charset_conv = false) : string
// *
// * @param int $uid                    UID сообщения
// * @param string $part                Номер части
// * @param rcube_message_part $o_part    Объект детали, созданный get_structure()
// * @param mixed $print                Верно для печати части, ресурс для записи содержимого части в указатель файла
// * @param resource $fp                Указатель файла для сохранения части сообщения
// * @param boolean $skip_charset_conv    Отключает преобразование кодировки
// *
// * @return string    Сообщение / тело части, если не напечатано
//            */
//            // в цикле разберём части сообщения и записываем в массив $msg1_parts каждую часть в свой ключ $part,
//            // если частей нет - PHP выдаёт предупреждение 'Invalid argument supplied for foreach()' - нет переменной $value
//            foreach ($msg_headers->structure->parts as $part => $msg_part) {
//                // Получаем части сообщения.
//                $msg_parts[$part] = array(
//                    'message' =>$Storage->get_message_part($msg_uid, $part, null, null, null, false),// Сообщение
//                    'filename'=>$msg_part->filename // Имя вложенного файла
//                    //$Storage->get_message_part($msg_uid, $part, null, null, null, false);
//                );
//                // удалим переменые
//                unset($msg_part);
//            }
//
//            // удалим переменые
//            //unset($part);
//            /// Разбираем первое сообщение. Конец
//
//            // Запакуем сообщение в двумерный массив.
//            $msgs[$msg_uid] = array(
//                'message_header'=> $msg_headers,
//                'message_parts' => $msg_parts
//            );
//        }
//        // очстим оставшееся переменные сообщения от последней интерации цикла
//        //unset($msg_headers, $msg_uid, $msg_offset, $msg2_offset, $Storage, $uids);
//        //unset($msg_headers, $msg_parts);
//
//        // json_encode — Возвращает JSON - представление данных.
//        $msgs_json = json_encode($msgs);
//
//        /**
// * Пример #2 Пример удаления cookie посредством setcookie()
// * Чтобы удалить cookie достаточно в качестве срока действия указать какое - либо время
// * в прошлом. Это запустит механизм браузера, удаляющий истёкшие cookie.
// * В примерах ниже показано, как удалить cookie, заданные в предыдущих примерах:
//        */
//        // установка даты истечения срока действия на час назад
//        //setcookie('TestCookie0', '', time() - 3600);
//        //setcookie('TestCookie', "", time() - 3600, " / ~rasmus / ", "example.com", 1);
//
//        // Вывести одно конкретное значение cookie
//        //$cook0 = $_COOKIE['test_cookie0'];
//        //$cook1 = $_COOKIE['test_cookie1'];
//        //$cook2 = $_COOKIE['test_cookie2'];
//        //
//        //$cook0u = $_COOKIE['testu_cookie0'];
//        //$cook1u = $_COOKIE['testu_cookie1'];
//        //$cook2u = $_COOKIE['testu_cookie2'];
//        //
//        //$value0 = 'кука 0';
//        //$value1 = 'кука 1';
//        //$value2 = 'кука 2';
//        //
//        //setcookie('test_cookie0', $uids);
//        //setcookie('test_cookie1', $uids, time() + 60);  /* срок действия 1 час */
//        //setcookie('test_cookie2', $uids, time() + 60, ' / ~rasmus123 / ', 'example.com', 1);
//        //
//        //rcube_utils::setcookie('testu_cookie0', $uids);
//        //rcube_utils::setcookie('testu_cookie1', $uids, time() + 60);
//        //rcube_utils::setcookie('testu_cookie2', $uids, time() + 60, ' / ~rasmus124 / ', 'example.com', 1);
//
//        /**
// * Установить переменную среды
// *
// * @param string $name Имя свойства
// * @param mixed $value Значение свойства
//        */
//        // передадим значение переменной в клиентскую среду (браузер)
//        $this->rc->output->set_env('msgs_json', $msgs_json);
//
//        // очстим оставшееся переменные сообщения от последней интерации цикла
//        //unset($msg_marked, $folder);
//
//        /**
// * Добавить локализованную метку в клиентскую среду (браузер).
// * Обертка для add_label(), добавляющая ID плагина как домен.
// * Синтаксис: 'plugin.lbl_get_msg' - наша локализованная метка.
//        */
//        //$this->rc->output->add_label('plugin.lbl_get_msg');
//
//        /**
// * Вызов клиентского метода
// *
// * @param string Метод для вызова
// * @param ...    Дополнительные аргументы
// *
// * Команда передаётся браузеру функцией - send()
// * Синтаксис: 'plugin.get_msg' - команда выполняемая в браузере.
//        */
//        $this->rc->output->command('plugin.get_msg');
//
//        // Функция отправки вывода клиенту, после этого работа PHP - скрипта заканчивается.
//        // Отправим данные в клиентскую часть (браузеру).
//        $this->rc->output->send();

?>