<?php

//Расширяем наш класс от класса rcube_plugin
class rm_duplicate_messages extends rcube_plugin
{
    // Объявляем глобальные переменные
    //
    /**
    * Инициализация плагина.
    */
    function init ()
    {
        /**
        * Это реализует шаблон проектирования singleton.
        *
        * @param integer $mode Игнорируемый аргумент rcube :: get_instance ()
        * @param string  $env  Имя среды для запуска (например, live, dev, test)
        *
        * @return rcmail Единственный и неповторимый экземпляр
        */
        // Переменная $this относится к текущему классу и представляет собой неявный объект.
        // rc - свойство этого объекта. Запишем туда системные настройки приложения.
        $this->rc = rcmail::get_instance();
        // переменной $tsk присвоим имя текущей задачи приложения
        //$tsk = $this->rc->task;
        // если задача 'mail' и действие '' или 'list', покажем нашу кнопку на панели, в других случаях не показываем
        if ($this->rc->task == 'mail' && ($this->rc->action == '' || $this->rc->action == 'list')) {
            /**
            * Регистрируем хуки сервера
            * Способ работы хуков плагинов заключается в том, что в разное время, пока Roundcube обрабатывает, он проверяет,
            * есть-ли у каких-либо плагинов зарегистрированные функции для запуска в это время, и если да, то функции запускаются
            * (путем выполнения «ловушки»). Эти функции могут изменять или расширять поведение Roundcube по умолчанию.
            * Регистрация хуков:     $this->add_hook('hook_name', $callback_function);
            * где второй аргумент – это обратный вызов PHP (функция в этом файле ниже), который может ссылаться на простую функцию или метод
            * объекта. Зарегистрированная функция получает один хеш-массив в качестве аргумента, который содержит определенные данные текущего
            * контекста в зависимости от ловушки.
            * См. «Перехватчики подключаемых модулей» для получения полного описания всех перехватчиков и их полей аргументов.
            * Аргумент var может быть изменен функцией обратного вызова и может (даже частично) быть возвращен приложению.
            */
            /**
            * preferences_save
            * Позволяет плагину вводить данные в массив настроек, которые будут сохранены
            * Аргументы:
            * @param prefs: Хеш-массив с сохраняемыми prefs
            * Возвращаемые значения:
            * @return result: логический
            * @return abort: логическое
            * @return prefs: массив
            */
            $this->add_hook('preferences_save', array($this,'save_settings1'));
            /**
            * preferences_update
            * В отличие от хука preferences_save, он запускается всякий раз, когда пользовательские настрой-ки обновляются. И это не ограничивается разделом настроек, но также может выполняться другим плагином.
            * Аргументы:
            * @param prefs: хеш-массив с префиксом, который нужно обновить
            * @param old: массив хешей с текущими сохраненными пользовательскими настройками
            * @param userid: ID пользователя, для которого сохраняются эти настройки.
            * Возвращаемые значения:
            * @return prefs: массив
            * @return old: массив
            * @return abort: логическое
            */
            $this->add_hook('preferences_update', array($this,'update_settings1'));
            /**
            * Загрузка локализованных текстов из каталога обрабатываемого плагина.
            *
            * @param string $dir        Каталог для поиска
            * @param mixed  $add2client Сделать тексты доступными на клиенте (массив со списком или true для всех)
            *
            * Вызываем функцию локализации - add_texts() из родительского класса интерфейса плагинов - rcube_plugin
            * Файл локализации добавляется в общий массив $texts, в массиве находятся ярлыки добавляемые клиенту
            * localization - это имя папки, в массиве указываем ключи из массива файла локализации
            * Метод add_texts() записывает файл локализации нашего плагина в общий массив локализации
            */
            // Добавим наши локализованные метки на страницу настроек плагина
            $this->add_texts('localization', array(
                    'lbl1',
                    'lbl2',
                    'lbl3',
                    'lbl4',
                    'lbl5',
                    'lbl6',
                    'lbl7',
                    'lbl8',
                    'lbl9',
                    'lbl10',
                    'lbl11',
                    'lbl_msg_request',
                    'lbl_get_msg',
                    'successful'
                ));
            // Функция интеграции скина нашего плагина, в общий скин системы. Загружаем файл скина плагина
            //$this->includeCSS();
            $this->include_stylesheet($this->local_skin_path() . '/rm_duplicate_messages.css');
            /**
            * Клиентские скрипты и элементы пользовательского интерфейса.
            * Конечно, плагины имеют большее отношение, чем просто отслеживание событий на стороне сервера.
            * API плагина также позволяет расширить пользовательский интерфейс и функциональность клиента.
            * Первый шаг – добавить код JavaScript на определенную страницу/действие.
            * Сделаем этот файл javascript доступным для клиента
            * Создадим файл сценария в папке вашего плагина, а затем включите его в init() метод вашего класса плагина с помощью
            * $this->include_script('client.js');
            *
            * @param string $fn путь к файлу; абсолютный или относительный к каталогу плагина
            */
            // добавим код JavaScript на определенную страницу / действие, создадим файл сценария в папке вашего плагина,
            // а затем включим его в init() метод вашего класса плагина с помощью $this->include_script()
            $this->include_script('rm_duplicate_messages.js');
            /**
            * Добавим кнопку в определенный контейнер на страницу (в панель управления на верху, toolbar)
            *
            * @param array  $param      Хеш-массив с именованными параметрами (используемый в скинах)
            * @param string $container  Имя контейнера, куда нужно добавить кнопки
            */
            // добавим командную кнопку на страницу в контейнер toolbar
            $this->add_button(
                array(
                    'domain'  => $this->ID,// id = "rcmbtn106"
                    'type'=> 'link',// тип кнопки
                    'label'=> 'lbl1',// локализованная надпись на кнопке
                    'title'=> 'lbl2',// локализованная всплывающая подсказка
                    'command'=> 'plugin.btn_cmd_msg_request',// имя выполняемой команды для кнопки
                    'width'=> 32,// ширина
                    'height'=> 32,// высота
                    'class'=> 'button btn_cmd',// класс стиля командной кнопки
                    'classact'=> 'button btn_cmd',// класс стиля командной кнопки в нажатом, активном состоянии
                ),
                'toolbar'); // панель управления - на верху (toolbar)
            /**
            * Зарегистрируем обработчик для определенного действия ajax (запроса) от клиента.
            * Обратный вызов будет выполнен по запросу типа /?_task=mail&_action=plugin.action
            * @param string $action   Имя действия (_task = mail& _action = plugin.action) (должно быть уникальным)
            * @param mixed  $callback Callback-Функция обратного вызова в виде строки со ссылкой на объект и именем метода:
            *                           строка с именем глобальной функции (или массивом) обратного вызова ($obj, 'methodname')
            *                           или массив со ссылкой на объект и именем метода
            * @param mixed  $callback Функция обратного вызова в виде строки или массив со ссылкой на объект и именем метода
            * @param string $owner    Имя плагина, регистрирующего это действие
            * @param string $task     Имя задачи, зарегистрированное этим плагином
            * Пример: $this->register_action('$action', $callback'));
            *         $this->register_action('$action',  array($this,'function'));
            */
            //$this->register_action('plugin.msg_request', array($this,'msg_request'));
            $this->register_action('plugin.msg_request', array($this,'msg_request'));
        }
        // когда наша функция запускается - страница обновляется, функцию обратного вызова требуется зарегистрировать еще раз
        elseif ($this->rc->action == 'plugin.msg_request') {
            $this->register_action('plugin.msg_request', array($this,'msg_request'));
            $this->add_hook('preferences_save', array($this,'save_settings1'));
        }
    }

    // Функция получает список uids из массива $_POST и сохранияет конфигурацию.
    function save_settings1($args)
    {
        // из глобального массива 'POST' получаем 'uids' выделенных сообщений
        //$uids = rcmail::get_uids(null, null, $multifolder, rcube_utils::INPUT_POST);
        // из глобального массива 'POST' получаем имя текущей папки '_mbox'
        //$folder = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST);

        //$folder   = $_POST['_mbox'];
        $args['prefs']['rm_duplicate_messages'] = 'my_var1';
        //$when = $_POST['_whenauto'];
        //$args['prefs']['whenauto3'] = 'my_var2';

        return $args;
    }

    function update_settings1 ($args)
    {
        $args['prefs']['rm_duplicate_messages'] = 'my_var1';

        return $args;
    }

    // Функция запрашивает очередное сообщение из базы и передаёт в клиентскую часть.
    function msg_request()
    {


        $this->rc->plugins->exec_hook('preferences_save', array(
                'abort'  => 'false',
                'prefs'  => '',
                'section'=> 'rm_duplicate_messages')
        );

//        $this->rc->plugins->exec_hook('preferences_save', array(
//                'abort'  => 'false',
//                'prefs'  => '',
//                'section'=> 'rm_duplicate_messages'));
//
//        $plugin = rcmail::get_instance()->plugins->exec_hook('preferences_save',
//            array(
//                'abort'  => 'false',
//                'prefs'  => '',
//                'section'=> 'rm_duplicate_messages'));


        /**
        * Инициализация и получение объекта хранения писем
        *
        * @return rcube_storage Storage        Объект хранения
        */
        $storage = $this->rc->get_storage();

        /**
        * Цикл получения заголовков сообщения по текущему 'uid'.
        * Циклом foreach перебираем вложенный массив '$uids[$folder]' и получаем 'uid'
        * каждого отдельного сообщения, присвоим это значение переменной '$msg_uid'.
        */
        foreach ($uids[$folder] as $msg_uid) {
            // Разбираем сообщение. Начало
            /**
            * Получение заголовков сообщений и структуры тела с сервера и построение структуры объекта,
            * подобной той, которая создается PEAR::Mail_mimeDecode
            *
            *     get_message (int $uid, string $folder = null): object
            *
            * @param int $uid        UID сообщения для получения
            * @param string    $folder Папка для чтения
            *
            * @return object rcube_message_header Данные сообщения
            */
            // получаем заголовки сообщения
            $msg_headers = $storage->get_message($msg_uid, $folder);

            // если сообщение имеет флаг 'DUBLIKAT' - пропустим это сообщение (начнём новую интерацию текущего цикла)
            if (isset($msg_headers->flags['DUBLIKAT'])) {
                // увеличим счётчики первого и второго сообщения и повторяем весь цикл
                //$msg_offset++;
                //$msg2_offset = $msg_offset + 1;
                // очищаем массивы и переменные первого и второго сообщения, функция unset()
                //unset($msg_headers, $msg_uid);
                // начнём цикл заново
                continue;
            }

            /**
            * Получаем тело определенного сообщения с сервера
            *
            *     get_message_part(int $uid, string $part   = 1, \rcube_message_part $o_part = null, mixed $print = null, resource $fp = null, boolean $skip_charset_conv = false) : string
            *
            * @param int $uid                    UID сообщения
            * @param string $part                Номер части
            * @param rcube_message_part $o_part    Объект детали, созданный get_structure()
            * @param mixed $print                Верно для печати части, ресурс для записи содержимого части в указатель файла
            * @param resource $fp                Указатель файла для сохранения части сообщения
            * @param boolean $skip_charset_conv    Отключает преобразование кодировки
            *
            * @return string    Сообщение / тело части, если не напечатано
            */
            // в цикле разберём части сообщения и записываем в массив $msg1_parts каждую часть в свой ключ $part,
            // если частей нет - PHP выдаёт предупреждение 'Invalid argument supplied for foreach()' - нет переменной $value
            foreach ($msg_headers->structure->parts as $part => $msg_part) {
                // Получаем части сообщения.
                $msg_parts[$part] = array(
                    'message' =>$storage->get_message_part($msg_uid, $part, null, null, null, false),// Сообщение
                    'filename'=>$msg_part->filename // Имя вложенного файла
                    //$storage->get_message_part($msg_uid, $part, null, null, null, false);
                );
                // удалим переменые
                unset($msg_part);
            }

            // удалим переменые
            //unset($part);
            /// Разбираем первое сообщение. Конец

            // Запакуем сообщение в двумерный массив.
            $msgs[$msg_uid] = array(
                'message_header'=> $msg_headers,
                'message_parts' => $msg_parts
            );
        }
        // очстим оставшееся переменные сообщения от последней интерации цикла
        //unset($msg_headers, $msg_uid, $msg_offset, $msg2_offset, $storage, $uids);
        //unset($msg_headers, $msg_parts);

        // json_encode — Возвращает JSON - представление данных.
        $msgs_json = json_encode($msgs);

        /**
        * Пример #2 Пример удаления cookie посредством setcookie()
        * Чтобы удалить cookie достаточно в качестве срока действия указать какое-либо время
        * в прошлом. Это запустит механизм браузера, удаляющий истёкшие cookie.
        * В примерах ниже показано, как удалить cookie, заданные в предыдущих примерах:
        */
        // установка даты истечения срока действия на час назад
        //setcookie('TestCookie0', '', time() - 3600);
        //setcookie('TestCookie', "", time() - 3600, " / ~rasmus / ", "example.com", 1);

        // Вывести одно конкретное значение cookie
        //$cook0 = $_COOKIE['test_cookie0'];
        //$cook1 = $_COOKIE['test_cookie1'];
        //$cook2 = $_COOKIE['test_cookie2'];
        //
        //$cook0u = $_COOKIE['testu_cookie0'];
        //$cook1u = $_COOKIE['testu_cookie1'];
        //$cook2u = $_COOKIE['testu_cookie2'];
        //
        //$value0 = 'кука 0';
        //$value1 = 'кука 1';
        //$value2 = 'кука 2';
        //
        //setcookie('test_cookie0', $uids);
        //setcookie('test_cookie1', $uids, time() + 60);  /* срок действия 1 час */
        //setcookie('test_cookie2', $uids, time() + 60, ' / ~rasmus123 / ', 'example.com', 1);
        //
        //rcube_utils::setcookie('testu_cookie0', $uids);
        //rcube_utils::setcookie('testu_cookie1', $uids, time() + 60);
        //rcube_utils::setcookie('testu_cookie2', $uids, time() + 60, ' / ~rasmus124 / ', 'example.com', 1);




        /**
        * Установить переменную среды
        *
        * @param string $name Имя свойства
        * @param mixed $value Значение свойства
        */
        // передадим значение переменной в клиентскую среду (браузер)
        $this->rc->output->set_env('msgs_json', $msgs_json);

        // очстим оставшееся переменные сообщения от последней интерации цикла
        //unset($msg_marked, $folder);

        /**
        * Добавить локализованную метку в клиентскую среду (браузер).
        * Обертка для add_label(), добавляющая ID плагина как домен.
        * Синтаксис: 'plugin.lbl_get_msg' - наша локализованная метка.
        */
        //$this->rc->output->add_label('plugin.lbl_get_msg');

        /**
        * Вызов клиентского метода
        *
        * @param string Метод для вызова
        * @param ...    Дополнительные аргументы
        *
        * Команда передаётся браузеру функцией - send()
        * Синтаксис: 'plugin.get_msg' - команда выполняемая в браузере.
        */
        $this->rc->output->command('plugin.get_msg');

        // Функция отправки вывода клиенту, после этого работа PHP - скрипта заканчивается.
        // Отправим данные в клиентскую часть (браузеру).
        $this->rc->output->send();
    }

    /**
    * Пишем отладочную информацию в log-файл.
    * file_put_contents — Пишет данные в файл
    * print_r — Выводит удобочитаемую информацию о переменной
    * Если filename не существует, файл будет создан.
    * Иначе, существующий файл будет перезаписан, за исключением случая, если указан флаг FILE_APPEND
    */
    protected function write_log_file ($args)
    {
        file_put_contents(
            $this->home . '/logs/rmduplicate.log',
            print_r($args, true),
            //print_r( / n, true),
            FILE_APPEND);
    }
}