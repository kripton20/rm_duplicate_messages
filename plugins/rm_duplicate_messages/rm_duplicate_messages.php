<?php

//Расширяем наш класс от класса rcube_plugin
class rm_duplicate_messages extends rcube_plugin
{
    // Инициализация плагина.
    function init ()
    {
        /**
        * Это реализует шаблон проектирования singleton. Получаем экземпляр объекта.
        * @param integer $mode   Игнорируемый аргумент rcube :: get_instance ().
        * @param string  $env    Имя среды для запуска (например, live, dev, test).
        * @return rcmail         Единственный и неповторимый экземпляр.
        */
        // Переменная $this относится к текущему классу и представляет собой неявный объект.
        // rc - свойство этого объекта. Запишем туда системные настройки приложения.
        $this->rc = rcmail::get_instance();
        // Если задача 'mail' и действие '' или 'list', покажем нашу кнопку на панели, в других случаях не показываем.
        if ($this->rc->task == 'mail' && ($this->rc->action == '' || $this->rc->action == 'list')) {
            /**
            * Регистрируем хуки сервера.
            * Способ работы хуков плагинов заключается в том, что в разное время, пока Roundcube обрабатывает,
            * он проверяет, есть-ли у каких-либо плагинов зарегистрированные функции для запуска в это время,
            * и если да, то функции запускаются (путем выполнения «ловушки»). Эти функции могут изменять или расширять
            * поведение Roundcube по умолчанию.
            * Синтаксис регистрации хуков:     $this->add_hook('hook_name', $callback_function);
            * где второй аргумент – это обратный вызов PHP (функция в этом файле ниже), который может ссылаться на простую
            * функцию или метод объекта. Зарегистрированная функция получает один хеш-массив в качестве аргумента, который
            * содержит определенные данные текущего контекста в зависимости от ловушки.
            * См. «Перехватчики подключаемых модулей» для получения полного описания всех перехватчиков и их полей аргументов.
            * Аргумент var может быть изменен функцией обратного вызова и может (даже частично) быть возвращен приложению.
            * Список хуков содержится в массиве: $this->$api->$handlers
            * /
            /**
            * Регистрация функции 'preferences_update'.
            * В отличие от хука preferences_save, он запускается всякий раз, когда пользовательские настройки обновляются.
            * И это не ограничивается разделом настроек, а также может выполняться другим плагином.
            * @param string prefs     Хеш-массив с префиксом, который нужно обновить
            * @param array old        Массив хешей с текущими сохраненными пользовательскими настройками
            * @param integer userid   ID пользователя, для которого сохраняются эти настройки.
            * @return array prefs     Массив новых данных.
            * @return array old       Имеющийся массив данных которые нужно обновить.
            * @return bulean abort    Логическое значение.
            */
            // Срабатывание функции 'update_settings' требуется при каждой перезагрузке страницы,
            // поэтому зарегистрируем хук с этой функцией в функции 'init', при срабатывании вышеуказанного условия.
            $this->add_hook('preferences_update', array($this,'update_settings'));
            /**
            * Загрузка локализованных текстов из каталога обрабатываемого плагина.
            * @param string $dir          Каталог для поиска
            * @param mixed  $add2client   Сделать тексты доступными на клиенте (массив со списком или true для всех)
            *
            * Вызываем функцию локализации - add_texts() из родительского класса интерфейса плагинов - rcube_plugin,
            * файл локализации добавляется в общий массив $texts, в массиве находятся надписи добавляемые клиенту.
            * localization - это имя папки, в массиве. Указываем ключи из массива файла локализации.
            * Метод add_texts() записывает файл локализации нашего плагина в общий массив локализации.
            */
            // Добавим наши локализованные надписи на страницу Roundcube.
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
                    'lbl12',
                    'lbl13',
                    'lbl14',
                    'lbl15',
                    'lbl16',
                    'lbl17',
                    'lbl18',
                    'lbl19',
                    'lbl20',
                    'lbl21',
                    'lbl22',
                    'lbl23',
                    'lbl24',
                    'lbl25',
                    'lbl26',
                    'lbl27',
                    'lbl28',
                    'lbl29',
                    //'lbl_msg_request',
                    //'lbl_get_msg',
                    //'successful'
                ));
            /**
            * Функция include_stylesheet() - функция интеграции скина плагина, в общий скин системы.
            * Укажите путь к текущей выбранной папке скинов в каталоге плагина с откатом к папке скинов по умолчанию.
            * @return string   Путь к скину относительно каталога плагинов.
            *
            * Функция  local_skin_path() указывает путь к текущей выбранной папке скина, установленного по-умолчанию
            * в каталоге плагина, с откатом к папке скинов по умолчанию.
            * @return string   Путь к скину относительно каталога плагинов.
            */
            // Загружаем файл скина по - умолчанию для нашего плагина (skins / larry / rm_duplicate_messages.css).
            $this->include_stylesheet($this->local_skin_path() . '/rm_duplicate_messages.css');
            // Загружаем файл общей таблицы стилей CSS для нашего плагина ().
            $this->include_stylesheet('http://localhost/rc147/plugins/rm_duplicate_messages/css/rm_duplicate_messages.css');
            /**
            * Клиентские скрипты и элементы пользовательского интерфейса.
            * Конечно, плагины имеют большее отношение, чем просто отслеживание событий на стороне сервера.
            * API плагина также позволяет расширить пользовательский интерфейс и функциональность клиента.
            * Первый шаг – добавить код JavaScript на определенную страницу/действие.
            * Сделаем этот файл javascript доступным для клиента: создадим файл сценария в папке вашего плагина, а затем
            * включите его в init() метод нашего класса плагина с помощью - $this->include_script('client.js');
            * @param string $fn   Путь к файлу: абсолютный или относительный к каталогу плагина.
            */
            $this->include_script('rm_duplicate_messages.js');
            /**
            * Добавим командную кнопку на страницу в определенный контейнер (в панель управления на верху страницы, toolbar).
            * @param array  $param       Хеш-массив с именованными параметрами (используемый в скинах).
            * @param string $container   Имя контейнера, куда нужно добавить кнопки.
            */
            $this->add_button(
                array(
                    'domain'  => $this->ID,// ID - элемента (id = "rcmbtn106").
                    'type'=> 'link',// Тип кнопки.
                    'label'=> 'lbl1',// Локализованная надпись на кнопке.
                    'title'=> 'lbl2',// Локализованная всплывающая подсказка.
                    'command'=> 'plugin.btn_cmd_toolbar',// Имя выполняемой команды для кнопки.
                    'width'=> 32,// Ширина кнопки.
                    'height'=> 32,// Высота кнопки.
                    'class'=> 'button btn_cmd',// Класс стиля командной кнопки.
                    'classact'=> 'button btn_cmd',// Класс стиля командной кнопки в нажатом, - активном состоянии.
                ),
                'toolbar'); // Панель управления - на верху (toolbar).
            /**
            * Зарегистрируем обработчик для определенного действия ajax - запроса от клиента.
            * Обратный вызов будет выполнен по запросу типа /?_task=mail&_action=plugin.action.
            * @param string $action     Имя действия (_task = mail& _action = plugin.action) (должно быть уникальным).
            * @param mixed  $callback   Callback-Функция обратного вызова в виде строки со ссылкой на объект и именем метода:
            *                           строка с именем глобальной функции (или массивом) обратного вызова ($obj, 'methodname')
            *                           или массив со ссылкой на объект и именем метода.
            * @param mixed $callback    Функция обратного вызова в виде строки или массив со ссылкой на объект и именем метода.
            * @param string $owner      Имя плагина, регистрирующего это действие.
            * @param string $task       Имя задачи, зарегистрированное этим плагином.
            * Пример: $this->register_action('$action', $callback'));
            *         $this->register_action('$action',  array($this,'function()'));
            */
            //$this->register_action('plugin.msg_save_prefs', array($this,'msg_save_prefs'));
            //$this->register_action('plugin.msg_save_prefs_remove', array($this,'msg_save_prefs_remove'));
            // Когда наша функция 'msg_save_prefs' запускается - страница обновляется,
            // - функцию обратного вызова требуется зарегистрировать еще раз.
        }elseif ($this->rc->action == 'plugin.msg_save_prefs') {
            $this->register_action('plugin.msg_save_prefs', array($this,'msg_save_prefs'));
        }elseif ($this->rc->action == 'plugin.msg_save_prefs_remove') {
            $this->register_action('plugin.msg_save_prefs_remove', array($this,'msg_save_prefs_remove'));
        }elseif ($this->rc->action == 'plugin.msg_request') {
            $this->register_action('plugin.msg_request', array($this,'msg_request'));
        }
    }

    // Системная функция перезаписи (обновления) пользовательских настроек текущего пользователя в хранилище:
    // - обновление массива 'prefs', при перезагрузке страницы.
    function update_settings ($args)
    {
        // Если в массиве 'args' имеются наши данные то просто перезапишем массив 'args'.
        if (isset($args['old']['rm_duplicate_messages'])) {
            // Перезапишем наши данные в массиве '$args' из раздела 'old' в раздел 'prefs'.
            $args['prefs']['rm_duplicate_messages'] = $args['old']['rm_duplicate_messages'];
        }
        // Вернём полученное значение в вызывающую функцию.
        return $args;
    }

    // Функция сохраняет настройки поиска и обработки писем - в массиве пользовательских настроек 'prefs'.
    function msg_save_prefs()
    {
        // В условии проверяем - что передаётся в массиве 'POST':
        // если передаётся параметр 'user_prefs_null' -
        // то массиву 'user_prefs['rm_duplicate_messages'] присвоим NULL'.
        if (isset($_POST['_user_prefs_null'])) {
            // Удалим ранее созданные наши записи (настройки поиска и обработки писем)
            // - в массиве пользовательских настроек 'prefs'.
            $user_prefs['rm_duplicate_messages'] = NULL;
            // Функция - прослушиватель события работы функции 'msg_save_prefs' об удалении ранее сохранённых
            // пользовательских настроек поиска писем в массиве 'prefs'.
            $this->rc->output->command('plugin.confirm_msg_save_prefs_remove');
        }else {
            // Из глобального массива 'POST' получаем список - 'uids' сообщений, переданных из браузера.
            $uids = rcmail::get_uids(null, null, $multifolder, rcube_utils::INPUT_POST);
            // Из глобального массива 'POST' получаем имя текущей папки - '_mbox'.
            $folder = $_POST['_mbox'];
            // Преобразуем двумерный массив 'uids' в одномерный массив.
            $uids   = $uids[$folder];
            // Запишем настройки обработки писем в массив пользовательских настроек 'prefs'.
            // Сохраним туда массив 'uids', имя текущей папки 'folder'
            // и переменную указывающую состояние командной кнопки - 'btn_cmd_toolbar (TRUE | FALSE)'.
            $user_prefs['rm_duplicate_messages'] = array(
                // Идентификаторы сообщений.
                'uids'=>$uids,
                // Имя текущей папки - '_mbox'.
                'folder'=>$folder,
                // Состояние командной кнопки: TRUE - работает, FALSE - неработает.
                'btn_cmd_toolbar'=>FALSE,
                // Два счётчика смещения по массиву.
                'msg1_offset'=>0,//$msg1_offset,
                'msg2_offset'=>1,//$msg2_offset
                // Колличество обрабатываемых сообщений: все сообщения, выделенные.
                'msg_sum'=>$_POST['_msg_sum'],
                // Режим обработки найденных дубликатов писем: отмечать, удалять.
                'msg_process_mode'=>$_POST['_msg_process_mode'],
                // Режим работы плагина: через браузер, серверный вариант.
                'plg_process_mode'=>$_POST['_plg_process_mode']
            );
            /**
            * Вызов клиентского метода.
            * @param string   Метод для вызова
            * @param ...      Дополнительные аргументы
            * Команда передаётся браузеру функцией - send().
            * Синтаксис: 'plugin.msg_handle' - команда выполняемая в браузере.
            */
            // Функция - прослушиватель события работы функции 'msg_save_prefs' о завершении сохранения
            // пользовательских настроек поиска писем в массиве 'prefs'.
            $this->rc->output->command('plugin.confirm_msg_save_prefs');
        }
        // Создадим объект 'rc_user' как экземпляр класса 'rcube_user',
        // и передадим ему идентификатор текущего пользователя - $this->rc->user->ID.
        $RC_user = new rcube_user($this->rc->user->ID);
        // Вызываем метод 'save_prefs' объекта 'rc_user' класса 'rcube_user' с параметром 'user_prefs'
        // в качестве данных которые нужно сохранить в массив пользовательских настроек 'prefs'.
        $RC_user->save_prefs($user_prefs);

        // Посылаем сигнал браузеру о завершении сохранения настроек в массиве 'prefs':
        /**
        * Установим переменную среды браузера
        * @param string $name   Имя свойства
        * @param mixed $value   Значение свойства
        */
        // Передадим значение переменной в клиентскую среду (браузер).
        //$this->rc->output->set_env('msgs_json', $msgs_json);

        // Добавим локализованную метку в клиентскую среду (браузер).
        // Обертка для add_label(), добавляющая ID плагина как домен.
        // Синтаксис: 'plugin.lbl25' - наша локализованная метка.
        //$this->rc->output->add_label('plugin.lbl_get_msg');
        //$this->rc->output->add_label('rm_duplicate_messages.lbl25');
        //$this->rcmail->output->add_label('rm_duplicate_messages.lbl25');

        // Функция отправки вывода клиенту, после этого работа PHP - скрипта заканчивается.
        // Отправим данные в клиентскую часть (браузеру).
        $this->rc->output->send();
    }

    // Функция поиска дубликатов, согласно пользовательским настройкам текущего пользователя из хранилища (массив 'prefs'):
    // - запрашивает очередные два сообщения из базы и - сравнивает их, выполняет установленные процедуры с
    // найденным дубликатом.
    function msg_request()
    {
        // Получаем пользовательские настройки текущего пользователя из хранилища (массив 'prefs'),
        // наши ранее сохранённые данные.
        $cfg_rm_duplicate = $this->rc->config->get('rm_duplicate_messages');
        // В услови проверяем значение переменной 'cfg_rm_duplicate':
        //     если переменная 'cfg_rm_duplicate' равна 'NULL' - значит в массиве 'prefs' настроек нет.
        if ($cfg_rm_duplicate == NULL) {
            // Вызываем функцию записи настроек.
            //            $this->msg_save_prefs();
            //            // Снова получаем пользовательские настройки текущего пользователя из хранилища.
            //            $cfg_rm_duplicate = $this->rc->config->get('rm_duplicate_messages');
            // Закончим работу плагина.
            exit;
        }

        // Получаем значения настроек:
        // Счётчик смещения первого и второго сообщений.
        $msg1_offset = $cfg_rm_duplicate['msg1_offset'];
        $msg2_offset = $cfg_rm_duplicate['msg2_offset'];

        // Текущий 'uids' сообщения.
        $msg_uid1    = $cfg_rm_duplicate['uids'][$cfg_rm_duplicate['msg1_offset']];
        $msg_uid2    = $cfg_rm_duplicate['uids'][$cfg_rm_duplicate['msg2_offset']];

        // Цикл выполняет только две итерации.
        //for ($i = 0; $i < 2; $i++) {
        // Этот цикл получает 'uid' сообщения.
        //            foreach ($cfg_rm_duplicate['uids'] as $uid1) {
        //                $uid = $uid1;
        //            }
        
        /**
        * array_slice — выбирает срез массива
        * Описание:
        * array_slice (array $array, int $offset, int|null $length = null, bool $preserve_keys = false):array
        * array_slice() возвращает последовательность элементов массива array,
        *               определённую параметрами offset и length.
        * @param array    Входной массив.
        * @param offset   Если параметр offset неотрицательный, последовательность начнётся на указанном
        *                 расстоянии от начала array.
        */
        //$msg1        = array_slice($cfg_rm_duplicate['uids'], 0, 1);
        //$msg2        = array_slice($cfg_rm_duplicate['uids'], 1, 1);
        $a           = 1;

        // Текущая папка.
        $folder      = $cfg_rm_duplicate['folder'];
        
        /**
        * Инициализация и получение объекта хранения писем.
        * @return rcube_storage   Объект хранения (Storage)
        */
        $storage     = $this->rc->get_storage();
        /**
        * Получение заголовков сообщений и структуры тела с сервера и построение структуры объекта,
        * подобной той, которая создается PEAR::Mail_mimeDecode.
        * Синтаксис: get_message (int $uid, string $folder = null): object
        * @param int $uid         UID сообщения для получения
        * @param string $folder   Папка для чтения
        * @return object $rcube_message_header Данные сообщения
        */
        // Получаем заголовок сообщения первого письма: указываем 'uid' и папку первого письма.
        $msg1_headers= $storage->get_message($msg_uid1, $folder);
        // Если первое сообщение имеет флаг 'DUBLIKAT' - пропустим это сообщение (начнём новую интерацию текущего цикла).
        if (isset($msg1_headers->flags['DUBLIKAT'])) {
            // увеличим счётчики первого и второго сообщения и повторяем весь цикл
            //$msg_offset++;
            //$msg2_offset = $msg_offset + 1;
            // очищаем массивы и переменные первого и второго сообщения, функция unset()
            //unset($msg_headers, $msg_uid);
            // начнём цикл заново
            //continue;
        }
        /**
        * Получаем тело определенного сообщения с сервера
        * get_message_part(int $uid, string $part = 1, \rcube_message_part $o_part = null, mixed $print = null, resource $fp = null, boolean $skip_charset_conv = false) : string
        * @param int $uid                     UID сообщения
        * @param string $part                 Номер части
        * @param rcube_message_part $o_part   Объект детали, созданный get_structure()
        * @param mixed $print                 Верно для печати части, ресурс для записи содержимого части в указатель файла
        * @param resource $fp                 Указатель файла для сохранения части сообщения
        * @param boolean $skip_charset_conv   Отключает преобразование кодировки
        * @return string                      Сообщение / тело части, если не напечатано
        */
        // В цикле разберём части сообщения и записываем в массив $msg1_parts каждую часть в свой ключ $part,
        // если частей нет - PHP выдаёт предупреждение 'Invalid argument supplied for foreach()' - нет переменной $value
        foreach ($msg1_headers->structure->parts as $part => $msg_part) {
            // Получаем части сообщения.
            $msg1_parts[$part] = array(
                // Сообщение
                'message'=>$storage->get_message_part($msg_uid1, $part, null, null, null, false),
                // Имя вложенного файла
                'filename'=>$msg_part->filename
            );
        }
        // Удалим переменые.
        unset($msg_part, $part);
        // Получаем заголовок сообщения второго письма: указываем 'uid' и папку второго письма.
        $msg2_headers = $storage->get_message($msg_uid2, $folder);
        if (isset($msg2_headers->flags['DUBLIKAT'])) {
            // увеличим счётчики первого и второго сообщения и повторяем весь цикл
            //$msg_offset++;
            //$msg2_offset = $msg_offset + 1;
            // очищаем массивы и переменные первого и второго сообщения, функция unset()
            //unset($msg_headers, $msg_uid);
            // начнём цикл заново
            //continue;
        }
        foreach ($msg2_headers->structure->parts as $part => $msg_part) {
            // Получаем части сообщения.
            $msg2_parts[$part] = array(
                'message' =>$storage->get_message_part($msg_uid2, $part, null, null, null, false),// Сообщение
                // Имя вложенного файла
                'filename'=>$msg_part->filename
                //$storage->get_message_part($msg_uid, $part, null, null, null, false);
            );
        }
        // Удалим переменые.
        unset($msg_part, $part);
        $a++;
        //}
        $stop4 = 4;



        // удалим переменые
        //unset($part);
        /// Разбираем первое сообщение. Конец

        // Запакуем сообщение в двумерный массив.
        //        $msgs[$msg_uid] = array(
        //            'message_header'=> $msg1_headers,
        //            'message_parts' => $msg_parts
        //        );
        // Конец функции msg_request.
    }
    //очстим оставшееся переменные сообщения от последней интерации цикла
    //    unset($msg1_headers, $msg_uid, $msg_offset, $msg2_offset, $storage, $uids);
    //    unset($msg1_headers, $msg_parts);

    //        // json_encode — Возвращает JSON - представление данных.
    //        $msgs_json = json_encode($msgs);
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
    // Далее следует обработка писем


    // Объявление защищённого метода - 'protected function'.
    // К protected (защищенным) свойствам и методам можно получить доступ либо из содержащего их
    // класса, либо из его подкласса. Никакому внешнему коду доступ к ним не предоставляется.
    /**
    * Функция записи отладочной информации в log-файл.
    * file_put_contents — Пишет данные в файл
    * print_r — Выводит удобочитаемую информацию о переменной
    * Если filename не существует, файл будет создан. Иначе, существующий файл будет
    * перезаписан, за исключением случая, если указан флаг FILE_APPEND
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