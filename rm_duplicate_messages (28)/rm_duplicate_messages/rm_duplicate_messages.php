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
        // Получаем пользовательские настройки текущего пользователя из хранилища (массив "prefs"),
        // наши ранее сохранённые данные.
        $cfg_rm_duplicate = $this->rc->config->get('rm_duplicate_messages');
        /**
        * Установим переменную среды пользователя (в браузере)
        * @param string $name   Имя свойства
        * @param mixed $value   Значение свойства
        */
        // передадим значение переменной в клиентскую среду (браузер)
        $this->rc->output->set_env('rm_duplicate_messages_uids', $cfg_rm_duplicate['uids']);
        // Если задача "mail" и действие '' или "list", покажем нашу кнопку на панели, в других случаях не показываем.
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
            // Срабатывание функции "update_settings" требуется при каждой перезагрузке страницы,
            // поэтому зарегистрируем хук с этой функцией в функции "init", при срабатывании вышеуказанного условия.
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
                    'successful'
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
            // Когда наша функция "msg_save_prefs" запускается - страница обновляется,
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
    // - обновление массива "prefs", при перезагрузке страницы.
    function update_settings ($args)
    {
        // Если в массиве "args" имеются наши данные то просто перезапишем массив "args".
        if (isset($args['old']['rm_duplicate_messages'])) {
            // Перезапишем наши данные в массиве "$args" из раздела "old" в раздел "prefs".
            $args['prefs']['rm_duplicate_messages'] = $args['old']['rm_duplicate_messages'];
        }
        // Вернём полученное значение в вызывающую функцию.
        return $args;
    }

    // Функция сохраняет настройки поиска и обработки писем - в массиве пользовательских настроек "prefs".
    function msg_save_prefs()
    {
        // В условии проверяем - что передаётся в массиве "POST":
        // если передаётся параметр "user_prefs_null" -
        // то массиву "user_prefs['rm_duplicate_messages'] присвоим NULL".
        if (isset($_POST['_user_prefs_null_save_log']) || isset($_POST['_user_prefs_null'])) {
            // Удалим ранее созданные наши записи (настройки поиска и обработки писем)
            // - в массиве пользовательских настроек "prefs".
            $user_prefs['rm_duplicate_messages'] = NULL;
            // Функция - прослушиватель события работы функции "msg_save_prefs" об удалении
            // ранее сохранённых пользовательских настроек поиска писем в массиве "prefs".
            $this->rc->output->command('plugin.confirm_msg_save_prefs_remove');
        }else {
            // Из глобального массива "POST" получаем имя текущей папки - "_mbox".
            $folder = $_POST['_mbox'];
            // Определяем сколько юидов брать:
            // Если в глобальном массиве "POST" передаётся значение "msg_all"
            // - тогда значения "uid" берём из функции PHP.
            if ($_POST['_uid'] == 'msg_all') {
                /**
                * Инициализируем и получим объект хранилище.
                * Синтаксис: get_storage()
                * @return rcube_storage $torage   Объект хранения.
                */
                $storage            = $this->rc->get_storage();
                /**
                * * Вернуть отсортированный список UID сообщений
                *
                * @param string $folder     Папка для получения индекса
                * @param string $sort_field Сортировать столбец
                * @param string $sort_order Порядок сортировки [ASC, DESC]
                * @param bool $no_threads   Получить индекс без потоков
                * @param bool $no_search    Получить индекс, не ограниченный результатом поиска (необязательно)
                * @return rcube_result_index|rcube_result_thread Список сообщений (UID)
                *
                * Синтаксис: index(string $folder = null, string $sort_field = null, string $sort_order = null) : \rcube_result_index | \rcube_result_thread
                */
                // Получим полный список "uids" сообщений в текущей папке отсортированный по алфавиту.
                $rcube_result_index = $storage->index($folder, $sort_field         = null, 'ASC');
                /**
                * Получим индексы всех сообщений.
                * @return array  Список идентификаторов сообщений
                */
                $uids = $rcube_result_index->get();
            }else {
                // Из глобального массива "POST" получаем список - "uids" сообщений, переданных из браузера.
                $uids = rcmail::get_uids(null, null, $multifolder, rcube_utils::INPUT_POST);
            }
            // Дополнительное условие: если массив $uids содержит или не содержит папку. Преобразуем к единому виду.
            if ($uids[$folder]) {
                $uids_result = $uids[$folder];
            }else {
                $uids_result = $uids;
            }
            // Сформируем массив пользовательских настроек "prefs" и запишем туда настройки обработки писем.
            // Сохраним туда массив "uids", имя текущей папки "folder" и переменную указывающую
            // состояние командной кнопки - "btn_cmd_toolbar (TRUE | FALSE)".
            $user_prefs['rm_duplicate_messages'] = array(
                // Идентификаторы сообщений.
                'uids'=>$uids_result,
                // Имя текущей папки - "_mbox".
                'folder'=>$folder,
                // Состояние командной кнопки: TRUE - работает, FALSE - неработает.
                'btn_cmd_toolbar'=>FALSE,
                // Два счётчика смещения по массиву: в виде массива для обработки в цикле.
                'msg_offset'=>array(
                    // Первое письмо от конца списка писем.
                    'msg1'=>0,
                    // Следующее письмо от конца списка писем.
                    'msg2'=>1
                ),
                // Порядок обрабатываемых сообщений: все сообщения или только выделенные.
                'msg_processing'=>$_POST['_msg_processing'],
                // Режим обработки найденных дубликатов писем: отмечать, удалять.
                'msg_process_mode'=>$_POST['_msg_process_mode'],
                // Режим работы плагина: через браузер или серверный вариант.
                'plg_process_mode'=>$_POST['_plg_process_mode'],
                // Подсчитываем колличество обработанных сообщений.
                'msg_marked'=>0
            );
            // Записываем лог - файл - формируем следующие данные:
            // дата, время, имя текущей папки, почтовый ящик, режим обработки сообщений.
            // Сформируем строку для передачи в функцию "write_log_file".
            // Сформируем строку для записи в файл.
            // "\t", горизонтальная табуляция (HT или 0x09 (9) в ASCII).
            // Текущая дата и время.
            /**
            * date — Форматирует вывод системной даты/времени
            * date (string $format, int|null $timestamp = null):string
            * Возвращает строку, отформатированную в соответствии с указанным шаблоном format.
            *  Используется метка времени, заданная аргументом timestamp, или текущее системное время,
            *  если timestamp не задан. Таким образом, timestamp является необязательным и по умолчанию
            *  равен значению, возвращаемому функцией time().
            */
            // Получим глобальные переменные.
            $server_script_name = $_SERVER['SCRIPT_NAME'];
            // Обрезаем строку. Вместо массива - используем список.
            list($a, $server_folder) = explode('/', $server_script_name);
            list($server_folder, $c) = explode('/', $server_folder);
            // Дата 2001.03.10 17:16:18 (формат MySQL DATETIME).
            $args = date("Y.m.d") . " ";
            // Время (17:16:18).
            $args .= date("H:i:s") . "\t";
            // Сигнал начала обработки.
            $args .= "Start ";
            // Имя хоста, порт, папка расположения приложения. $_SERVER['SERVER_PORT'] .
            $args .= $_SERVER['HTTP_HOST'] . ":" . "/" . $server_folder . " ";
            // Почтовый ящик "mailbox = ".
            $args .= $this->rc->user->data['username'] . " ";
            // Текущая папка.
            $args .= "mailbox_folder=" . $folder . " ";
            // Номер текущей страницы.
            $args .= "mailbox_page=" . $this->rc->storage->list_page . " ";
            // Определяем колличество сообщений поставленных в обработку.
            $args .= "msg_sum_uids=" . count($user_prefs['rm_duplicate_messages']['uids']) . " ";
            // Порядок обрабатываемых сообщений: все сообщения или только выделенные "msg_processing = ".
            //$args .= $user_prefs['rm_duplicate_messages']['msg_processing'] . " ";
            // Режим обработки найденных дубликатов писем "msg_process_mode = ": отмечать или удалять.
            //$args .= $user_prefs['rm_duplicate_messages']['msg_process_mode'] . "\n";
            // Режим работы плагина: через браузер или серверный вариант.
            //$args .= "plg_process_mode = " . $user_prefs['rm_duplicate_messages']['plg_process_mode'] . "\n";
            // Вызываем функцию записи лог - файла.
            $this->write_log_file($args);
            /**
            * Вызов функции корая выполняется на стороне клиента.
            * @param string   Метод для вызова
            * @param ...      Дополнительные аргументы
            * Команда передаётся браузеру функцией - send().
            * Синтаксис: "plugin.msg_handle" - команда выполняемая в браузере.
            */
            // Функция - прослушиватель события работы функции "msg_save_prefs" о завершении
            // сохранения пользовательских настроек поиска писем в массиве "prefs".
            $this->rc->output->command('plugin.confirm_msg_save_prefs');
        }
        // Записываем собранные данные в массив "prefs".
        // Создадим объект "rc_user" как экземпляр класса "rcube_user",
        // и передадим ему идентификатор текущего пользователя - $this->rc->user->ID.
        $rc_user = new rcube_user($this->rc->user->ID);
        // Вызываем метод "save_prefs" объекта "rc_user" класса "rcube_user" с параметром "user_prefs"
        // в качестве данных которые нужно сохранить в массив пользовательских настроек "prefs".
        $rc_user->save_prefs($user_prefs);
        // Функция отправки вывода клиенту, после этого работа PHP - скрипта заканчивается.
        // Отправим данные в клиентскую часть (браузеру).
        $this->rc->output->send();
    }

    // Функция поиска дубликатов, согласно пользовательским настройкам текущего пользователя
    // из хранилища (массив "prefs"): запрашивает очередные два сообщения из базы и - сравнивает их,
    // выполняет установленные процедуры с найденным дубликатом.
    function msg_request()
    {
        // Добавим нашу локализованную надпись в сообщение.
        $this->add_texts('localization', array('lbl30'));
        // Получаем пользовательские настройки текущего пользователя из хранилища (массив "prefs"),
        // наши ранее сохранённые данные.
        $cfg_rm_duplicate = $this->rc->config->get('rm_duplicate_messages');
        // В услови проверяем значение переменной "cfg_rm_duplicate":
        // если переменная "cfg_rm_duplicate" равна "NULL" - значит в массиве "prefs" настроек нет.
        if ($cfg_rm_duplicate == NULL) {
            // Закончим работу плагина. Нужно дописать вывод сообщения.
            exit;
        }
        // Текущая папка.
        $folder = $cfg_rm_duplicate['folder'];
        // Определяем колличество обрабатываемых сообщений.
        // В условии проверяем: какие сообщения обрабатываем.
        if ($cfg_rm_duplicate['msg_processing'] == 'msg_selected') {
            //  Если обрабатываем только выделенные сообщения.
            $msg_sum_uids = count($cfg_rm_duplicate['uids']);
        }else {
            //  Если обрабатываем все сообщения.
            $msg_sum_uids = count($cfg_rm_duplicate['uids']);
        }
        // Переменной "msg_marked" присвоим ссылку на элемент массива "msg_marked".
        $msg_marked =&$cfg_rm_duplicate['msg_marked'];
        /**
        * Инициализируем и получим объект хранилище.
        * Синтаксис: get_storage()
        * @return rcube_storage $torage   Объект хранения.
        */
        //$storage = $this->rc->get_storage();
        // Получаем значения счётчиков смещения первого и второго сообщений.
        // Цикл выполняет только две итерации.
        foreach ($cfg_rm_duplicate['msg_offset'] as $key => $msg_offset) {
            // Текущий "uids" сообщения.
            //$msg_uid = $cfg_rm_duplicate['uids'][$folder][$msg_offset];
            $msg_uid = $cfg_rm_duplicate['uids'][$msg_offset];
            // Получаем объект "MESSAGE" как экземпляр класса "rcube_message".
            //$folder = "2020_2";
            $MESSAGE = new rcube_message($msg_uid, $folder);
            // Если сообщение имеет флаг "DUBLIKAT":
            // - пропустим это сообщение и начнём новую интерацию текущего цикла.
            if (isset($MESSAGE->headers->flags['DUBLIKAT'])) break;
            // Получаем адреса:
            // Инициализируем переменные.
            $data_from = NULL;
            $data_to = NULL;
            $data_cc = NULL;
            $data_replyto = NULL;
            $data_in_reply_to = NULL;
            $data_mdn_to = NULL;
            $data_references = NULL;
            // В условиях проверяем есть - необходимые параметры в переменной "$MESSAGE".
            if ($MESSAGE->headers->from != NULL) {
                // Получаем заголовок "От кого".
                $data_from = $MESSAGE->headers->from;
                // Очищаем от лишних символов.
                $data_from = $this->clear_adress($data_from);
            }
            if ($MESSAGE->headers->to != NULL) {
                // Получаем заголовок "Кому".
                $data_to = $MESSAGE->headers->to;
                // Очищаем от лишних символов.
                $data_to = $this->clear_adress($data_to);
            }
            if ($MESSAGE->headers->cc != NULL) {
                // Получаем заголовок "Скрытый".
                $data_cc = $MESSAGE->headers->cc;
                // Очищаем от лишних символов.
                $data_cc = $this->clear_adress($data_cc);
            }
            if ($MESSAGE->headers->replyto != NULL) {
                // Получаем заголовок "Ответиь".
                $data_replyto = $MESSAGE->headers->replyto;
                // Очищаем от лишних символов.
                $data_replyto = $this->clear_adress($data_replyto);
            }
            if (($MESSAGE->headers->in_reply_to != NULL) || ($MESSAGE->headers->in_reply_to != "")) {
                // Получаем заголовок "Ответиь".
                $data_in_reply_to = $MESSAGE->headers->in_reply_to;
                // Очищаем от лишних символов.
                //$data_in_reply_to = $this->clear_adress($data_in_reply_to);
            }
            if ($MESSAGE->headers->mdn_to != NULL) {
                // Получаем заголовок "mdn_to".
                $data_mdn_to = $MESSAGE->headers->mdn_to;
                // Очищаем от лишних символов.
                $data_mdn_to = $this->clear_adress($data_mdn_to);
            }
            if (($MESSAGE->headers->references != NULL) || ($MESSAGE->headers->references != "")) {
                // Получаем заголовок "references".
                $data_references = $MESSAGE->headers->references;
                // Очищаем от лишних символов.
                $data_references = $this->clear_adress($data_references);
            }
            /**
            * rcube_message_header::from_array() - заводской метод создания экземпляров заголовков из массива данных.
            * @param array                   Хеш-массив со значениями заголовков
            * @return rcube_message_header   Экземпляр объекта, заполненный значениями заголовков
            *
            * Условный оператор ?, возвращает y, в случае если x принимает значение true,
            * и z в случае, если x принимает значение false.
            * x ? y : z
            */
            // Получаем объект "$MESSAGE_HEADERS" как экземпляр класса "rcube_message_header",
            // с заголовками текущего сообщения.
            //$MESSAGE_HEADERS = rcube_message_header::from_array($MESSAGE->headers);
            // Запишем в масив тело письма и заголовки.
            $msgs[$key] = array(
                // Дата отправки.
                // Преобразуем в число через системную функцию "strtotime" из класа "rcube_utils".
                'date'=> rcube_utils::strtotime($MESSAGE->headers->date),
                // Тема письма.
                // Декодируем через системную функцию "decode_header" из класа "rcube_mime".
                'subject'=> trim(rcube_mime::decode_header($MESSAGE->headers->subject, $header->charset)),
                // Заголовок "От кого".
                'from'=> $data_from ? $data_from : NULL,
                // Заголовок "Кому".
                'to'=> $data_to ? $data_to : NULL,
                // Заголовок "Скрытый".
                'cc'=> $data_cc ? $data_cc : NULL,
                // Заголовок "Ответиь".
                'replyto'=> $data_replyto ? $data_replyto : NULL,
                'in_reply_to'=> $data_in_reply_to ? $data_in_reply_to : NULL,
                'references' => $data_references ? $data_references : NULL,
                // Приоритет.
                //'priority'=> $MESSAGE->headers->priority,
                'mdn_to'=> $data_mdn_to ? $data_mdn_to : NULL,
                // Флаги письма.
                'flags'=> $MESSAGE->headers->flags,
                // uid текущего сообщения.
                'uid'=>$MESSAGE->uid
            );
            $a=4;
        }
        // Цикл "while" будет работать если сформировались оба письма в массиве "msgs".
        // нужно определить существование переменной msgs
        while (isset($msgs['msg1']) & isset($msgs['msg2'])) {
            // В условии сравниваем заголовки и вложение двух писем.
            /**
            * Сравниваем сообщения:
            * Функкция strcmp — Бинарно - безопасное сравнение строк с учетом регистра символов
            * Функкция strcasecmp — Бинарно - безопасное сравнение строк без учета регистра символов
            * Описание: strcmp(string $str1, string $str2):int
            * Пример: $var1 = "Hello"; $var2 = "hello";
            *     if (strcmp($var1, $var2) == 0) {
            *     echo '$var1 равно $var2 при регистрозависимом сравнении';
            *     }
            */
            // Пояснения к заголовкам сообщений здесь: http://www.antispam.ru / 4user / reading - email - headers - translation.shtml
            // Сначала сравниваем дату сообщения потом остальные заголовки, потом вложения.
            // Заголовок указывает дату создания и отправления сообщения.
            if ($msgs['msg1']['date'] == $msgs['msg2']['date']) {
                // Сравниваем все остальные заголовки.
                // Отправитель сообщения (От).
                If (strcasecmp($msgs['msg1']['from'], $msgs['msg2']['from']) == 0) {
                    // Создадим пустой массив и присвоим ему значение NULL.
                    $attachments = array();
                    $attachments['msg1']['msg1'] = NULL;
                    $attachments['msg2']['msg2'] = NULL;
                    foreach ($cfg_rm_duplicate['msg_offset'] as $key => $msg_offset) {
                        // Проверим есть - ли части сообщения.
                        if ($MESSAGE->mime_parts) {
                            // Вызываем функцию определения частей сообщения.
                            $mime_parts = $this->mime_parts($MESSAGE, $key);
                            // Пополняем ассоциативный массив новыми элементами (ключ=>значение).
                            for ($i = 0; $i < 1; $i++) {
                                // Тело письма.
                                $msgs[$key]['body'] = $mime_parts[$key]['body'];
                                // Тело письма html - версии.
                                $msgs[$key]['body_html'] = $mime_parts[$key]['body_html'];
                            }
                        }
                        // В условии проверяем есть - ли вложения в письме:
                        if (count($MESSAGE->attachments)) {
                            // Вызываем функцию получения вложений.
                            $attachments[$key] = $this->attachments($MESSAGE, $key);
                            // Пополняем ассоциативный массив новыми элементами (ключ=>значение).
                            //                    for ($i = 0; $i < 1; $i++) {
                            //                        // Имя вложенного файла.
                            //                        $msgs[$key]['filename'] = $attachments[$key]['filename'];
                            //                        // Размер вложенного файла.
                            //                        $msgs[$key]['filesize'] = $attachments[$key]['filesize'];
                            //                    }
                        }
                    }
                    // Получатель сообщения (Кому).
                    If (strcasecmp($msgs['msg1']['to'], $msgs['msg2']['to']) == 0
                        // Дополнительные получатели сообщения (Копия)
                        && strcasecmp($msgs['msg1']['cc'], $msgs['msg2']['cc']) == 0
                        // Адрес для ответов.
                        && strcasecmp($msgs['msg1']['replyto'], $msgs['msg2']['replyto']) == 0
                        // Показывает, что сообщение относится к типу "ответ на ответ".
                        && strcasecmp($msgs['msg1']['in_reply_to'], $msgs['msg2']['in_reply_to']) == 0
                        // Тема сообщения
                        && strcasecmp($msgs['msg1']['subject'], $msgs['msg2']['subject']) == 0
                        // Тело сообщения
                        && strcmp($msgs['msg1']['body'], $msgs['msg2']['body']) == 0
                        // HTML - версия тела сообщения
                        && strcmp($msgs['msg1']['body_html'], $msgs['msg2']['body_html']) == 0
                        // Используется в Usenet для прослеживания "дерева ответов", к которому принадлежит данное сообщение.
                        && strcmp($msgs['msg1']['references'], $msgs['msg2']['references']) == 0) {
                        // Свободный заголовок, устанавливающий приоритет сообщения. (приоритет не учитываем)
                        //&& strcmp($msgs['msg1']['priority'], $msgs['msg2']['priority']) == 0) {
                        // Сравниваем вложения сообщений:
                        // В условии проверяем если длина массивов вложений первого и второго сообщений одинаковая
                        // тогда выполняем сравнение массивов, можно использовать array_diff().
                        if ($attachments['msg1'].count() == $attachments['msg2'].count()) {
                            // Инициализируем и получим объект хранилище.
                            $storage = $this->rc->get_storage();
                            // Если первое сообщение содержит флаг "SEEN": уберём его из наших массивов.
                            if (isset($msgs['msg1']['flags']['SEEN'])) unset($msgs['msg1']['flags']['SEEN']);
                            // Если первое сообщение содержит флаг "JUNK": уберём его из наших массивов.
                            if (isset($msgs['msg1']['flags']['JUNK'])) unset($msgs['msg1']['flags']['JUNK']);
                            // Если первое сообщение содержит флаг "NONJUNK": уберём его из наших массивов.
                            if (isset($msgs['msg1']['flags']['NONJUNK'])) unset($msgs['msg1']['flags']['NONJUNK']);
                            // Если первое сообщение содержит флаг "UNKNOWN - 0": уберём его из наших массивов.
                            if (isset($msgs['msg1']['flags']['UNKNOWN-0'])) unset($msgs['msg1']['flags']['UNKNOWN-0']);
                            // Если второе сообщение содержит флаг "SEEN": уберём его из наших массивов.
                            if (isset($msgs['msg2']['flags']['SEEN'])) unset($msgs['msg2']['flags']['SEEN']);
                            // Если второе сообщение содержит флаг "JUNK": уберём его из наших массивов.
                            if (isset($msgs['msg2']['flags']['JUNK'])) unset($msgs['msg2']['flags']['JUNK']);
                            // Если второе сообщение содержит флаг "NONJUNK": уберём его из наших массивов.
                            if (isset($msgs['msg2']['flags']['NONJUNK'])) unset($msgs['msg2']['flags']['NONJUNK']);
                            // Если второе сообщение содержит флаг "UNKNOWN - 0": уберём его из наших массивов.
                            if (isset($msgs['msg2']['flags']['UNKNOWN-0'])) unset($msgs['msg2']['flags']['UNKNOWN-0']);
                            // Проверяем флаги сообщений:
                            // если флаги одинаковые - отметим второе сообщение.
                            if ($msgs['msg1']['flags'] == $msgs['msg2']['flags']) {
                                // В условии проверяем установленные настройки в массиве пользовательских настроек
                                // "prefs": - помечать сообщения или удалять сообщения.
                                /**
                                * Установим флаг сообщения для одного или нескольких сообщений.
                                * @param mixed $uids           UID сообщений в виде массива или строки,
                                *                              разделенной запятыми, или ' * '.
                                * @param string $flag          Флаг для установки: SEEN, UNSEEN, DELETED,
                                *                              UNDELETED, RECENT, ANSWERED, DRAFT, MDNSENT.
                                * @param string $folder        Имя папки.
                                * @param boolean $skip_cache   Истина, чтобы пропустить очистку кеша сообщений.
                                * @return boolean              Статус операции.
                                */
                                // Установим флаги "DELETED" и "DUBLIKAT".
                                if ($cfg_rm_duplicate['msg_process_mode'] == 'mark') {
                                    // В условии проверяем заголовок MDN - Message Disposition Notification,
                                    // уведомление об открытии сообщения.
                                    if ($msgs['msg1']['mdn_to'] == NUUL) {
                                        $storage->set_flag($msgs['msg1']['uid'], 'DELETED', $folder, true);
                                        $storage->set_flag($msgs['msg1']['uid'], 'DUBLIKAT', $folder, true);
                                        // Увеличим на единицу колличество обработанных сообщений.
                                        $msg_marked++;
                                    }else {
                                        $storage->set_flag($msgs['msg2']['uid'], 'DELETED', $folder, true);
                                        $storage->set_flag($msgs['msg2']['uid'], 'DUBLIKAT', $folder, true);
                                        // Увеличим на единицу колличество обработанных сообщений.
                                        $msg_marked++;
                                    }
                                }elseif ($cfg_rm_duplicate['msg_process_mode'] == 'del') {
                                    // В условии проверяем заголовок MDN - Message Disposition Notification,
                                    // уведомление об открытии сообщения.
                                    if ($msgs['msg2']['mdn_to'] == NULL) {
                                        // Удаляем сообщение.
                                        $storage->delete_message($msgs['msg2']['uid'], $folder);
                                        // Увеличим на единицу колличество обработанных сообщений.
                                        $msg_marked++;
                                    }else {
                                        // Удаляем сообщение.
                                        $storage->delete_message($msgs['msg1']['uid'], $folder);
                                        // Увеличим на единицу колличество обработанных сообщений.
                                        $msg_marked++;
                                    }
                                }
                                // Прерываем выполнение цикла и выходим.
                                break;
                                // Если у первого сообщения установлены следующие флаги:
                                // "ANSWERED", "FLAGGED" или "FORWARDED" и у второго сообщения эти флаги
                                // не установлены то установим флаги "DELETED" и "DUBLIKAT" на второе сообщение.
                            }elseif (
                                (
                                    isset($msgs['msg1']['flags']['ANSWERED']) ||
                                    isset($msgs['msg1']['flags']['FLAGGED']) ||
                                    isset($msgs['msg1']['flags']['FORWARDED']
                                    )
                                ) && !(
                                    isset($msgs['msg2']['flags']['ANSWERED']) ||
                                    isset($msgs['msg2']['flags']['FLAGGED']) ||
                                    isset($msgs['msg2']['flags']['FORWARDED']
                                    )
                                )
                            ) {
                                // В условии проверяем установленные настройки в массиве пользовательских настроек
                                // "prefs": - помечать сообщения или удалять сообщения.
                                if ($cfg_rm_duplicate['msg_process_mode'] == 'mark') {
                                    // Установим флаги "DELETED" и "DUBLIKAT".
                                    $storage->set_flag($msgs['msg2']['uid'], 'DELETED', $folder, true);
                                    $storage->set_flag($msgs['msg2']['uid'], 'DUBLIKAT', $folder, true);
                                    // Увеличим на единицу колличество обработанных сообщений.
                                    $msg_marked++;
                                }elseif ($cfg_rm_duplicate['msg_process_mode'] == 'del') {
                                    // Удаляем сообщение.
                                    $storage->delete_message($msgs['msg2']['uid'], $folder);
                                    // Увеличим на единицу колличество обработанных сообщений.
                                    $msg_marked++;
                                }
                                // Прерываем выполнение цикла и выходим.
                                break;
                                // Если у второго сообщения установлены следующие флаги:
                                // "ANSWERED", "FLAGGED" или "FORWARDED" и у первого сообщения эти флаги
                                // не установлены то установим флаги "DELETED" и "DUBLIKAT" на первое сообщение.
                            }elseif (
                                (
                                    isset($msgs['msg2']['flags']['ANSWERED']) ||
                                    isset($msgs['msg2']['flags']['FLAGGED']) ||
                                    isset($msgs['msg2']['flags']['FORWARDED']
                                    )
                                ) && !(
                                    isset($msgs['msg1']['flags']['ANSWERED']) ||
                                    isset($msgs['msg1']['flags']['FLAGGED']) ||
                                    isset($msgs['msg1']['flags']['FORWARDED'])
                                )
                            ) {
                                // В условии проверяем установленные настройки в массиве пользовательских настроек
                                // "prefs": - помечать сообщения или удалять сообщения.
                                if ($cfg_rm_duplicate['msg_process_mode'] == 'mark') {
                                    // Установим флаги "DELETED" и "DUBLIKAT".
                                    $storage->set_flag($msgs['msg1']['uid'], 'DELETED', $folder, true);
                                    $storage->set_flag($msgs['msg1']['uid'], 'DUBLIKAT', $folder, true);
                                    // Увеличим на единицу колличество обработанных сообщений.
                                    $msg_marked++;
                                }elseif ($cfg_rm_duplicate['msg_process_mode'] == 'del') {
                                    // Удаляем сообщение.
                                    $storage->delete_message($msgs['msg1']['uid'], $folder);
                                    // Увеличим на единицу колличество обработанных сообщений.
                                    $msg_marked++;
                                }
                                // Прерываем выполнение цикла и выходим.
                                break;
                            }
                            // Прерываем выполнение цикла и выходим.
                            break;
                        }
                        // Прерываем выполнение цикла и выходим.
                        break;
                    }
                    // Прерываем выполнение цикла и выходим.
                    break;
                }
                // Прерываем выполнение цикла и выходим.
                break;
            }
            // Прерываем выполнение цикла и выходим.
            break;
        }
        // Раздел увеличения счётчико сообщений: начало.
        // Если текущее сообщение это "msg2" то увеличим счётчик "msg2",
        // иначе увеличим счётчики первого и второго сообщений.
        if ($key == "msg2") {
            // Увеличим счётчик второго сообщения:
            // получим значение "msg2" из массива "cfg_rm_duplicate",
            $msg2 =&$cfg_rm_duplicate['msg_offset']['msg2'];
            // и увеличим это значение на единицу.
            $msg2++;
            // В условии проверяем если счётчик второго сообщения равен или превышает
            // колличество "uids" в списке то увеличим счётчики первого и второго сообщений.
            if ($msg2 >= $msg_sum_uids) {
                // Увеличим счётчики первого и второго сообщений:
                // получим значение "msg1" и "msg2" из массива "cfg_rm_duplicate" по ссылке через &.
                $msg1 =&$cfg_rm_duplicate['msg_offset']['msg1'];
                $msg2 =&$cfg_rm_duplicate['msg_offset']['msg2'];
                // Увеличим значение "msg1" на единицу.
                $msg1++;
                // Присвоим переменной "msg2" значение переменной "msg1" увеличинное на единицу.
                $msg2 = $msg1 + 1;
            }
            // Если текущее сообщение это "msg1" то увеличим счётчики первого и второго сообщений.
        }elseif ($key == "msg1") {
            // Увеличим счётчики первого и второго сообщений:
            // получим значение "msg1" и "msg2" из массива "cfg_rm_duplicate" по ссылке через &.
            $msg1 =&$cfg_rm_duplicate['msg_offset']['msg1'];
            $msg2 =&$cfg_rm_duplicate['msg_offset']['msg2'];
            // Увеличим значение "msg1" на единицу.
            $msg1++;
            // Присвоим переменной "msg2" значение переменной "msg1" увеличинное на единицу.
            $msg2 = $msg1 + 1;
        }
        // получим значение "msg1" и "msg2" из массива "cfg_rm_duplicate" по ссылке через &.
        $msg1 =&$cfg_rm_duplicate['msg_offset']['msg1'];
        // Раздел увеличения счётчико сообщений: конец.
        // Если "$msg1" равно "msg_sum_uids" уменьшенную на единицу, значит все uids из переданного списка обработаны и нужно завершить обработку писем
        // В этом случае команду "restart_msg_request" не посылаем.
        if ($msg1 >= $msg_sum_uids - 1) {
            // Получаем пользовательские настройки текущего пользователя из хранилища (массив "prefs"),
            // наши ранее сохранённые данные.
            //$cfg_rm_duplicate = $this->rc->config->get('rm_duplicate_messages');
            // Записываем лог - файл - формируем следующие данные:
            // дата, время, имя текущей папки, почтовый ящик, режим обработки сообщений.
            // Сформируем строку для передачи в функцию "write_log_file".
            // Сформируем строку для записи в файл.
            // "\t", горизонтальная табуляция (HT или 0x09 (9) в ASCII).
            // Текущая дата и время.
            /**
            * date — Форматирует вывод системной даты/времени
            * date (string $format, int|null $timestamp = null):string
            * Возвращает строку, отформатированную в соответствии с указанным шаблоном format.
            *  Используется метка времени, заданная аргументом timestamp, или текущее системное время,
            *  если timestamp не задан. Таким образом, timestamp является необязательным и по умолчанию
            *  равен значению, возвращаемому функцией time().
            */
            // Получим глобальные переменные.
            //$server_name = $_SERVER['SERVER_NAME'];
            //$server_port = $_SERVER['SERVER_PORT'];
            $server_script_name = $_SERVER['SCRIPT_NAME'];
            // Обрезаем строку. Вместо массива - используем список.
            list($a, $server_folder) = explode('/', $server_script_name);
            //list($server_folder, $c) = explode(' / ', $server_folder);
            // Дата 2001.03.10 17:16:18 (формат MySQL DATETIME).
            $args = date("Y.m.d") . " ";
            // Время (17:16:18).
            $args .= date("H:i:s") . "\t";
            // Сигнал окончания обработки.
            $args .= "End   ";
            // Имя хоста, порт, папка расположения приложения. $_SERVER['SERVER_PORT'] .
            $args .= $_SERVER['HTTP_HOST'] . ":" . "/" . $server_folder . " ";
            // Почтовый ящик  "mailbox = ".
            $args .= $this->rc->user->data['username'] . " ";
            // Текущая папка.
            $args .= "mailbox_folder=" . $folder . " ";
            // Номер текущей страницы.
            $args .= "mailbox_page=" . $this->rc->storage->list_page . " ";
            // Колличество обработанных сообщений.
            //$args .= "msg_marked=" . $cfg_rm_duplicate['msg_marked'] . "\n";
            // Вызываем функцию записи лог - файла.
            $this->write_log_file($args);
            // Удалим ранее созданные наши записи (настройки поиска и обработки писем)
            // - в массиве пользовательских настроек "prefs".
            $user_prefs['rm_duplicate_messages'] = NULL;
            // Вызов функции корая выполняется на стороне клиента.
            $this->rc->output->command('plugin.successful');
        }else {
            // Записываем собранные данные в массив "prefs".
            // Сформируем массив "user_prefs": берём имеющиеся данные.
            $user_prefs['rm_duplicate_messages'] = $cfg_rm_duplicate;
            // Вызов функции корая выполняется на стороне клиента.
            $this->rc->output->command('plugin.restart_msg_request');
            /**
            * Вызов команды display_message
            *     show_message(string $message, string $type = 'notice', array $vars = null, boolean $override = true, int $timeout)
            * Аргументы
            * @param string $message     Сообщение для отображения
            * @param string $type        Тип сообщения [notice|confirm|confirmation|error] (уведомление, подтвердить, подтверждение, ошибка)
            * @param array $vars         Пары "ключ-значение" должны быть заменены в локализованном тексте
            * @param boolean $override   Отменить последнее установленное сообщение
            * @param int $timeout        Время отображения сообщения в секундах
            */
            // Выводим сообщение о работе функции "msg_request".
            $this->rc->output->show_message($this->gettext('lbl30'), 'notice', $vars = NULL, $override = TRUE);
        }
        // Создадим объект "rc_user" как экземпляр класса "rcube_user",
        // и передадим ему идентификатор текущего пользователя - $this->rc->user->ID.
        $rc_user = new rcube_user($this->rc->user->ID);
        // Вызываем метод "save_prefs" объекта "rc_user" класса "rcube_user" с параметром "user_prefs"
        // в качестве данных которые нужно сохранить в массив пользовательских настроек "prefs".
        $rc_user->save_prefs($user_prefs);
        // Функция отправки вывода клиенту, после этого работа PHP - скрипта заканчивается.
        // Отправим данные в клиентскую часть (браузеру).
        $this->rc->output->send();
    }

    // Убираем лишние символы из адреса.
    function clear_adress ($data_adress)
    {
        /**
        * explode — Разбивает строку с помощью разделителя.
        * Описание:
        * explode ( string $separator , string $string , int $limit = PHP_INT_MAX ) : array
        * Возвращает массив строк, полученных разбиением строки string с использованием separator
        * в качестве разделителя.
        * @param undefined $data_adress
        * @return
        */
        // Вместо массива - используем список.
        list($a, $b) = explode('<', $data_adress);
        list($data_adress, $c) = explode('>', $b);
        return $data_adress;
    }

    // Функция определения частей сообщения.
    protected function mime_parts($MESSAGE, $key)
    {
        // В цикле разберём части сообщения и записываем в массив $msg1_parts каждую часть в свой ключ $part,
        // если частей нет - PHP выдаёт предупреждение "Invalid argument supplied for foreach()" - нет переменной $value.
        foreach ($MESSAGE->mime_parts as $part) {
            // По условию получаем соответствующие части письма.
            // Записываем в переменную "body" - body - версию (простой вариант).
            if ($part->mimetype === 'text/plain') $body = $MESSAGE->get_part_body($part->mime_id, true);
            // Записываем в переменную "html" - html - версию.
            if ($part->mimetype === 'text/html') $body_html = $MESSAGE->get_part_body($part->mime_id, true);
        }
        // Сформируем массив для возвращения.
        $mime_parts[$key] = array(
            // Тело письма.
            'body'=>$body,
            // Тело письма html - версии.
            'body_html'=>$body_html);
        // Вернём полученные значения.
        return $mime_parts;
    }

    // Функция определения фложений в сообщение.
    protected function attachments($MESSAGE, $key)
    {
        // В цикле перебираем вложения письма.
        foreach ($MESSAGE->attachments as $apart =>$attach_prop) {
            // Получаем вложения: имя файла и размер.
            $filename = rcmail_attachment_name($attach_prop, FALSE);
            $filesize = rcmail::get_instance()->message_part_size($attach_prop);
            // Запишем вложения - имя файла и размер в масив вложений - "attachment",
            // с указанием порядкового номера текущего письма в качестве ключа
            // для вложенного масссива.
            $attachments[$key] = array(
                // Имя вложенного файла.
                'filename'=>$filename,
                // Размер вложенного файла.
                'filesize'=>$filesize
            );
        }
        // Вернём полученные значения.
        return $attachments;
    }

    // Объявление защищённого метода - "protected function".
    // К protected (защищенным) свойствам и методам можно получить доступ либо из содержащего их
    // класса, либо из его подкласса. Никакому внешнему коду доступ к ним не предоставляется.
    // Запечатаем функцию "file_put_contents" в нами созданную функцию "write_log_file" с параметром "args".
    protected function write_log_file ($args)
    {
        // Пишем содержимое (строку) в файл,
        // используя флаг FILE_APPEND flag для дописывания содержимого в конец файла и флаг LOCK_EX
        // для предотвращения записи данного файла кем - нибудь другим в данное время.
        /**
        * Функция записи отладочной информации в log-файл.
        * file_put_contents — Пишет данные в файл
        * file_put_contents(string $filename , mixed $data , int $flags = 0 , resource $context = ?):int
        * Функция идентична последовательным успешным вызовам функций fopen(), fwrite() и fclose().
        * Если filename не существует, файл будет создан. Иначе, существующий файл будет
        * перезаписан, за исключением случая, если указан флаг FILE_APPEND.
        * @param filename   Путь к записываемому файлу.
        * @param data       Записываемые данные. Может быть типа string, array или ресурсом потока.
        *                   Если data является потоковым ресурсом (stream), оставшийся буфер этого потока
        *                   будет скопирован в указанный файл.
        *                   Это похоже на использование функции stream_copy_to_stream().
        *                   Также вы можете передать одномерный массив в качестве параметра data.
        *                   Это будет эквивалентно вызову file_put_contents($filename, implode('', $array)).
        * @param flags      Значением параметра flags может быть любая комбинация следующих флагов,
        *                   соединённых бинарным оператором ИЛИ (|).
        * Доступные флаги:  FILE_USE_INCLUDE_PATH - Ищет filename в подключаемых директориях. Подробнее смотрите
        * директиву include_path; FILE_APPEND - Если файл filename уже существует, данные будут дописаны в конец
        * файла вместо того, чтобы его перезаписать; LOCK_EX - Получить эксклюзивную блокировку на файл на время
        * записи. Другими словами, между вызовами fopen() и fwrite() произойдёт вызов функции flock().
        * Это не одно и то же, что вызов fopen() с флагом "x".
        */
        file_put_contents(
            $this->home . '/logs/rmduplicate.log',
            /**
            * print_r — Выводит удобочитаемую информацию о переменной.
            * Если вы хотите перехватить вывод print_r(), используйте параметр return. Если его значение равно true,
            * то print_r() вернёт информацию вместо вывода в браузер.
            */
            print_r($args, true),
            FILE_APPEND | LOCK_EX);
    }
}
