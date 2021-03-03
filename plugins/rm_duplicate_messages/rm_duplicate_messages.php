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
            // Функция - прослушиватель события работы функции 'msg_save_prefs' об удалении
            // ранее сохранённых пользовательских настроек поиска писем в массиве 'prefs'.
            $this->rc->output->command('plugin.confirm_msg_save_prefs_remove');
        }else {
            // Из глобального массива 'POST' получаем список - 'uids' сообщений, переданных из браузера.
            $uids = rcmail::get_uids(null, null, $multifolder, rcube_utils::INPUT_POST);
            // Из глобального массива 'POST' получаем имя текущей папки - '_mbox'.
            $folder = $_POST['_mbox'];
            // Преобразуем двумерный массив 'uids' в одномерный массив.
            $uids   = $uids[$folder];
            // Сортировка списка 'uids' сообщений в прямом порядке.
            sort($uids);
            // Запишем настройки обработки писем в массив пользовательских настроек 'prefs'.
            // Сохраним туда массив 'uids', имя текущей папки 'folder' и переменную указывающую
            // состояние командной кнопки - 'btn_cmd_toolbar (TRUE | FALSE)'.
            $user_prefs['rm_duplicate_messages'] = array(
                // Идентификаторы сообщений.
                'uids'=>$uids,
                // Имя текущей папки - '_mbox'.
                'folder'=>$folder,
                // Состояние командной кнопки: TRUE - работает, FALSE - неработает.
                'btn_cmd_toolbar'=>FALSE,
                // Два счётчика смещения по массиву: в виде массива для обработки в цикле.
                'msg_offset'=>array(
                    'msg1'=>0,
                    'msg2'=>1
                ),
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
            // Функция - прослушиватель события работы функции 'msg_save_prefs' о завершении
            // сохранения пользовательских настроек поиска писем в массиве 'prefs'.
            $this->rc->output->command('plugin.confirm_msg_save_prefs');
        }
        // Записываем собранные данные в массив 'prefs'.
        // Создадим объект 'rc_user' как экземпляр класса 'rcube_user',
        // и передадим ему идентификатор текущего пользователя - $this->rc->user->ID.
        $RC_user = new rcube_user($this->rc->user->ID);
        // Вызываем метод 'save_prefs' объекта 'rc_user' класса 'rcube_user' с параметром 'user_prefs'
        // в качестве данных которые нужно сохранить в массив пользовательских настроек 'prefs'.
        $RC_user->save_prefs($user_prefs);
        // Функция отправки вывода клиенту, после этого работа PHP - скрипта заканчивается.
        // Отправим данные в клиентскую часть (браузеру).
        $this->rc->output->send();
    }

    // Функция поиска дубликатов, согласно пользовательским настройкам текущего пользователя
    // из хранилища (массив 'prefs'): запрашивает очередные два сообщения из базы и - сравнивает их,
    // выполняет установленные процедуры с найденным дубликатом.
    function msg_request()
    {
        // Получаем пользовательские настройки текущего пользователя из хранилища (массив 'prefs'),
        // наши ранее сохранённые данные.
        $cfg_rm_duplicate = $this->rc->config->get('rm_duplicate_messages');
        // В услови проверяем значение переменной 'cfg_rm_duplicate':
        // если переменная 'cfg_rm_duplicate' равна 'NULL' - значит в массиве 'prefs' настроек нет.
        if ($cfg_rm_duplicate == NULL) {
            // Вызываем функцию записи настроек.
            //            $this->msg_save_prefs();
            //            // Снова получаем пользовательские настройки текущего пользователя из хранилища.
            //            $cfg_rm_duplicate = $this->rc->config->get('rm_duplicate_messages');
            // Закончим работу плагина.
            exit;
        }
        // Текущая папка.
        $folder     = $cfg_rm_duplicate['folder'];
        // Создадим массивы для обработки вложений писем:
        // вложения текущего письма.
        $attachment = array();
        // промежуточный массив.
        $att_result = array();
        // результирующий массив содержащий вложения писем с разделением по номеру письма.
        $msgs_attachment = array();
        // Получаем значения счётчиков смещения первого и второго сообщений.
        // Цикл выполняет только две итерации.
        foreach ($cfg_rm_duplicate['msg_offset'] as $key => $msg_offset) {
            // Текущий 'uids' сообщения.
            $msg_uid         = $cfg_rm_duplicate['uids'][$msg_offset];
            // Получаем объект 'MESSAGE' как экземпляр класса 'rcube_message'.
            $MESSAGE         = new rcube_message($msg_uid, $folder);
            /**
            * rcube_message_header::from_array() - заводской метод создания экземпляров заголовков из массива данных.
            * @param array                   Хеш-массив со значениями заголовков
            * @return rcube_message_header   Экземпляр объекта, заполненный значениями заголовков
            */
            // Получаем объект '$MESSAGE_HEADERS' как экземпляр класса 'rcube_message_header',
            // с заголовками текущего сообщения.
            $MESSAGE_HEADERS = rcube_message_header::from_array($MESSAGE->headers);
            // Если сообщение имеет флаг 'DUBLIKAT' - пропустим это сообщение (начнём новую интерацию текущего цикла).
            if (isset($MESSAGE->headers->flags['SEEN'])) {
                // Увеличим счётчик текущего сообщения и повторяем цикл.
                $msg_offset++;
                
                // очищаем массивы и переменные первого и второго сообщения, функция unset()
                //unset($msg_headers, $msg_uid);
                // начнём цикл заново
                continue;
            }
            // В цикле разберём части сообщения и записываем в массив $msg1_parts каждую часть в свой ключ $part,
            // если частей нет - PHP выдаёт предупреждение 'Invalid argument supplied for foreach()' - нет переменной $value.
            foreach ($MESSAGE->mime_parts as $part) {
                // По условию получаем соответствующие части письма.
                // Записываем в переменную 'body' - body - версию (простой вариант).
                if ($part->mimetype === 'text/plain') $body = $MESSAGE->get_part_body($part->mime_id, true);
                // Записываем в переменную 'html' - html - версию.
                if ($part->mimetype === 'text/html') $body_html = $MESSAGE->get_part_body($part->mime_id, true);
            }
            // Запишем в масив тело письма: 'body' и 'body_html' версии.
            $msgs[$key] = array(
                'body'       =>$body,
                'body_html'  =>$body_html,
                'subject'    => $MESSAGE_HEADERS->get('subject'),
                'from'       => $MESSAGE_HEADERS->get('from'),
                'to'         => $MESSAGE_HEADERS->get('to'),
                'cc'         => $MESSAGE_HEADERS->get('cc'),
                'replyto'    => $MESSAGE_HEADERS->get('replyto'),
                'in_reply_to'=> $MESSAGE_HEADERS->get('in_reply_to'),
                'date'       => $MESSAGE_HEADERS->get('date'),
                'references' => $MESSAGE_HEADERS->get('references'),
                'priority'   => $MESSAGE_HEADERS->get('priority'),
                'mdn_to'     => $MESSAGE_HEADERS->get('mdn_to'),
                'flags'      => $MESSAGE_HEADERS->get('flags')
            );
            // Удалим переменные 'body' и 'body_html'
            unset($body, $body_html);
            // В условии проверяем есть - ли вложения в письме.
            if (count($MESSAGE->attachments)) {
                // В цикле перебираем вложения письма.
                foreach ($MESSAGE->attachments as $apart =>$attach_prop) {
                    // Получаем вложения: имя файла и размер.
                    $filename = rcmail_attachment_name($attach_prop, FALSE);
                    $filesize = rcmail::get_instance()->message_part_size($attach_prop);
                    // Запишем вложения - имя файла и размер в масив вложений - 'attachment',
                    // с указанием порядкового номера текущего письма в качестве ключа
                    // для вложенного масссива.
                    $attachment[$key] = array(
                        // Делаем вложенный массив по частям сообщения: каждое вложение в свой раздел в массиве.
                        $apart    =>array(
                            // Имя вложенного файла.
                            'filename'=>$filename,
                            // Размер вложенного файла.
                            'filesize'=>$filesize
                        ));
                    // Удалим переменные: имя файла и размер файла.
                    unset($filename, $filesize);
                    // Вспомогательный массив для перезаписи масива вложений - 'msg_attachment'.
                    $att_tmp         = $msgs_attachment;
                    /**
                    * array_merge_recursive — Рекурсивное слияние одного или более массивов.
                    * Описание: array_merge_recursive(array ...$arrays):array
                    * Функция array_merge_recursive() сливает элементы двух или более массивов таким образом,
                    * что значения одного массива присоединяются в конец другого. Возвращает результирующий массив.
                    * Если входные массивы имеют одинаковые строковые ключи, то значения этих ключей сливаются в массив,
                    * и это делается рекурсивно, так что если одно из значений является массивом, то функция сливает его
                    * с соответствующим значением в другом массиве. Однако, если массивы имеют одинаковые числовые ключи то
                    * каждое последующее значение не заменит исходное значение, а будет добавлено в конец массива.
                    * @param arrays   Рекурсивно сливаемые массивы.
                    * @return array   Массив значений, полученный в результате слияния аргументов вместе.
                    *                 Если вызывается без аргументов, возвращает пустой array.
                    */
                    // Результирующий массив вложений с разделением по письмам с порядковым номером.
                    $msgs_attachment = array_merge_recursive($att_result, $attachment, $att_tmp);
                    // Удалим массив 'attachment' (вложения письма) перед выходом из цикла
                    // foreach - перебор вложений.
                    unset($attachment);
                }
            }
        }
        // Удалим наши вспомогательные переменные и масивы.
        unset($msg_uid, $apart, $attach_prop, $key, $part, $att_result, $att_tmp);
        $stop1     = 1;
        
        // Записываем собранные данные в массив 'prefs'.
        // Сформируем массив 'user_prefs':
        // берём имеющиеся данные.
        //$cfg_rm_duplicate
        
        // Создадим объект 'rc_user' как экземпляр класса 'rcube_user',
        // и передадим ему идентификатор текущего пользователя - $this->rc->user->ID.
        $RC_user = new rcube_user($this->rc->user->ID);
        // Вызываем метод 'save_prefs' объекта 'rc_user' класса 'rcube_user' с параметром 'user_prefs'
        // в качестве данных которые нужно сохранить в массив пользовательских настроек 'prefs'.
        $RC_user->save_prefs($user_prefs);


        // Конец функции msg_request.
        $stop_func = 1;
    }

// Это не работает, нужно звпустить msg_save_prefs
//// По ссылке изменим текущее значение счётчика смещения второго письма.
//$msg =&$cfg_rm_duplicate['msg_offset']['msg2'];
//$msg_offset++;
//$msg = $msg_offset;

///**
//* Установить переменную среды    * @param string $name Имя свойства    * @param mixed $value Значение свойства
//*/
//// передадим значение переменной в клиентскую среду (браузер)
////$this->rc->output->set_env('msgs_json', $msgs_json);
//
//// очстим оставшееся переменные сообщения от последней интерации цикла
////unset($msg_marked, $folder);
//
///**
//* Добавить локализованную метку в клиентскую среду (браузер).
//* Обертка для add_label(), добавляющая ID плагина как домен.
//* Синтаксис: 'plugin.lbl_get_msg' - наша локализованная метка.
//*/
////$this->rc->output->add_label('plugin.lbl_get_msg');
//
///**
//* Вызов клиентского метода    * @param string Метод для вызова    * @param ...    Дополнительные аргументы    * Команда передаётся браузеру функцией - send()    * Синтаксис: 'plugin.get_msg' - команда выполняемая в браузере.
//*/
////$this->rc->output->command('plugin.get_msg');
//
//// Функция отправки вывода клиенту, после этого работа PHP - скрипта заканчивается.
//// Отправим данные в клиентскую часть (браузеру).
////$this->rc->output->send();

//protected function time_work_script(){
//// Расчёт времени выполнения скрипта
//$start = microtime(true);
//$array = array();
//$i = $j = 0;
//
//$finish = microtime(true);
//$delta = $finish - $start;
//$result = $content . 'Время выполнения скрипта: ' . $delta . ' сек.';
//unset($content, $delta);
//}

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