// Функция сохраняет пользовательские настройки поиска и обработки писем текущего пользователя
// в хранилище пользовательских настроек - массив "prefs".
function msg_save_prefs() {
    // Переменные диалогового окна. Получаем надписи на локализованном языке из файла локализации.
    // Заголовок контента диалогового окна.
    var content = '<H3 align="center">'+rcmail.get_label('rm_duplicate_messages.lbl4')+'</H4><br/><div class="msg_save_prefs">'+
    // Первая рамка. Колличество обрабатываемых сообщений.
    '<div><fieldset><legend>'+rcmail.get_label('rm_duplicate_messages.lbl7')+'</legend>'+
    '<div class="processing_mode">'+
    '<input id="msg_processing_id0" name="msg_processing" type="radio" value="msg_all" checked="checked">'+rcmail.get_label('rm_duplicate_messages.lbl8')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl9')+'</div>'+
    '<div class="processing_mode">'+
    // Переключатель показывает изменённый текст.
    '<input id="msg_processing_id1" name="msg_processing" type="radio" onChange="innerdiv();" value="msg_selected" />'+rcmail.get_label('rm_duplicate_messages.lbl10')+'</div>'+
    '<div class="processing_mode_lbl">'+
    '<div id="innerdiv" class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl11')+'</div>'+
    '</div></fieldset></div>'+
    // Вторая рамка. Режим обработки найденных дубликатов писем.
    '<div><fieldset><legend>'+rcmail.get_label('rm_duplicate_messages.lbl13')+'</legend>'+
    '<div class="processing_mode"><input name="msg_process_mode" type="radio" value="mark">'+
    rcmail.get_label('rm_duplicate_messages.lbl14')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl15')+'</div>'+
    '<div class="processing_mode"><input name="msg_process_mode" type="radio" value="del" checked="checked">'+
    rcmail.get_label('rm_duplicate_messages.lbl16')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl17')+'<br /></div></fieldset></div>'+
    // Третья рамка. Режим работы плагина.
    '<div><fieldset><legend>'+rcmail.get_label('rm_duplicate_messages.lbl18')+'</legend>'+
    '<div class="processing_mode"><input name="plg_process_mode" type="radio" value="in_browser" checked="checked">'+
    rcmail.get_label('rm_duplicate_messages.lbl19')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl20')+'</div>'+
    '<div class="processing_mode"><input disabled name="plg_process_mode" type="radio" value="in_server">'+
    rcmail.get_label('rm_duplicate_messages.lbl21')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl22')+'</div></fieldset></div></div>';
    // Заголовок диалогового окна.
    var title = rcmail.get_label('rm_duplicate_messages.lbl3');
    // Кнопки диалогового окна.
    buttons={
    };
    // Кнопка "Запустить".
    buttons[rcmail.get_label('rm_duplicate_messages.lbl5')] = function(e) {
        // Получаем значения полей всплывающего окна с применением jQuery:
        // Порядок обрабатываемых сообщений: все сообщения или только выделенные.
        var msg_processing = $('input[name="msg_processing"]:checked').val();
        // Режим обработки найденных дубликатов писем: отмечать, удалять.
        var msg_process_mode = $('input[name="msg_process_mode"]:checked').val();
        // Режим работы плагина: через браузер, серверный вариант.
        var plg_process_mode = $('input[name="plg_process_mode"]:checked').val();
        // Закрываем диалоговое окно.
        $(this).remove();
        // Условие проверки - сколько "uids" нужно отправить в POST-запросе:
        // все какие есть в текущей папке, все на текущей странице, только выделенные на текущей странице.
        if (msg_processing=='msg_all') {
            // Отправляем все какие есть в текущей папке.
            // Значения "uids" возмём в скрипте PHP.
            var uids = 'msg_all';
        }else if(msg_processing=='msg_current_page'){
            // Отправляем все на текущей странице.
            // Объявляем пустой массив для записи  "uids".
            // Оператор "var" объявит массив для всей функции - он будет виден за пределами цикла.
            var uids = [];
            // Получаем значения "uids" списка писем текущей страницы согласно настройкам Roundcube файл -
            // config.inc.php, параметр - $config['mail_pagesize']:
            // В цикле получаем "uid" из объекта "rcmail.message_list.rows".
            for(uid in rcmail.message_list.rows){
                // Запишем каждое значение "uids" в массив "uids".
                // Функция push - Добавляет элемент в конец массива.
                uids.push(uid);
            }
        }else{
            // Только выделенные на текущей странице.
            // Получаем значения "uids" выделенных писем в списке.
            var uids = rcmail.message_list.selection;
        }
        // Условие проверки если значение "uids" не получено.
        if (!uids){
            // Выведем сообщение
            return window.alert('\n'+rcmail.get_label('rm_duplicate_messages.lbl23')+'\n'+'\n'+rcmail.get_label('rm_duplicate_messages.lbl24'));
            // Остановим работу функции.
            exit;
        }
        // Включаем блокировку интерфейса: выводим сообщение о работе процедуры.
        // Параметр "lock" для того чтобы это сообщение перекрывалось следующим сообщением
        // о выполняемых процедурах.
        // Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
        //   Первый параметр - "true" означает показывать сообщение или нет,
        //   второй параметр - "plugin_name.label" получает локализованную метку из массива языковых настроек.
        var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl25');
        // Передаём POST-запрос на сервер с указанием выполнить функцию сохранения настроек обработки писем
        // - "msg_save_prefs": вызываем метод "http_post" объекта "rcmail" (параметры через запятую),
        // метод "selection_post_data()" отправляет данные на сервер в массив [_POST] -
        // там содержатся передаваемые параметры из браузера.
        rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data(
                {
                    // Идентификаторы сообщений.
                    _uid: uids,
                    // Порядок обрабатываемых сообщений: все сообщения или только выделенные.
                    _msg_processing: msg_processing,
                    // Режим обработки найденных дубликатов писем: отмечать, удалять.
                    _msg_process_mode: msg_process_mode,
                    // Режим работы плагина: через браузер, серверный вариант.
                    _plg_process_mode: plg_process_mode
                }
            ), lock);
        // Отключим нашу коммандную кнопку
        window.rcmail.enable_command('plugin.btn_cmd_rm_dublecates', false);
    };
    // Кнопка "Сбросить настройки"
    buttons[rcmail.get_label('rm_duplicate_messages.lbl6')] = function(e) {
        // Закрываем окно
        $(this).remove();
        // Включаем блокировку интерфейса: выводим сообщение о работе процедуры.
        // Параметр "lock" для того чтобы это сообщение перекрывалось следующим сообщением
        // о выполняемых процедурах.
        // Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
        //   Первый параметр - "true" означает показывать сообщение или нет,
        //   второй параметр - "plugin_name.label" получает локализованную метку из массива языковых настроек.
        var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl27');
        // Посылаем на сервер команду стереть данные пользовательских настроек текущего пользователя
        // в хранилище.
        // Передаём POST-запрос на сервер с указанием выполнить функцию сохранения настроек обработки писем -
        // "msg_save_prefs": вызываем метод "http_post" объекта "rcmail" (параметры через запятую),
        // метод "selection_post_data()" отправляет данные на сервер в массив [_POST] -
        // там содержатся передаваемые параметры из браузера.
        rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data(
                {
                    // Передаём параметр указывающий функции "msg_save_prefs" удалить настройки
                    // поиска и обработки писем - в массиве пользовательских настроек "prefs".
                    _user_prefs_null: 'user_prefs_null'
                }
            ), lock);
    };
    // Кнопка отмены.
    buttons[rcmail.get_label('cancel')] = function(e) {
        // закрываем окно
        $(this).remove();
    };
    // Показываем диалоговое окно.
    rcmail.show_popup_dialog(content, title, buttons);
    // В услови проверяем выделены-ли письма в списке.
    if (rcmail.message_list.selection.length>1) {
        // Если выделены - ставим переключатель на "Выделенные".
        document.getElementById('msg_processing_id1').checked = true;
    }
}
// Функция вставляет новый текст при переключении переключателя.
function innerdiv(){
    // Вставим новый текст.
    document.getElementById('innerdiv').innerHTML=rcmail.get_label('rm_duplicate_messages.lbl12');
}
// Отправка команды на сервер для запуска обработки дубликатов писем.
function msg_request(){
    // Включаем блокировку интерфейса: выводим сообщение о работе процедуры.
    // Параметр "lock" для того чтобы это сообщение перекрывалось следующим сообщением
    // о выполняемых процедурах.
    // Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
    //   Первый параметр - "true" означает показывать сообщение или нет,
    //   второй параметр - "plugin_name.label" получает локализованную метку из массива языковых настроек.
    var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl29');
    // Передаём POST-запрос на сервер с указанием выполнить функцию сохранения настроек обработки писем
    // - "msg_save_prefs": вызываем метод "http_post" объекта "rcmail" (параметры через запятую),
    // метод "selection_post_data()" отправляет данные на сервер в массив [_POST] -
    // там содержатся передаваемые параметры из браузера.
    rcmail.http_post('plugin.msg_request', rcmail.selection_post_data({}), lock);
}
// Инициализируем объект "rcmail: rcube_webmail". ($(document) взято из jQuery.)
$(document).ready(function() {
        // Если инициализирован объект "window.rcmail" выполняем операторы в условии.
        if (window.rcmail) {
            /**
            * Добавление и регистрация слушателей событий:
            * Это делается с помощью следующих двух функций:
            *     rcmail.addEventListener|removeEventListener('event', callback);
            * Функция callback получает объект события в качестве одного аргумента.
            * Этот объект события содержит свойства, специфичные для события.
            * Они перечислены в качестве аргументов под соответствующим описанием события.
            *   Метод addEventListener()устанавливает функцию, которая будет вызываться всякий раз,
            * когда будет происходить указанное событие. Общими целями являются Element, Document, и Window.
            * Целью может быть любой объект, поддерживающий события (например XMLHttpRequest или объект формы).
            *      addEventListener() работает путем добавления функции или объекта, реализующего EventListener
            * в список прослушивателей событий для указанного типа события на том EventTarget,
            * на котором оно вызывается.
            * https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
            * Параметр "init" - событие, параметр "function(evt)" это - ананимная callback-функция.
            */
            // Прослушиватель события работы системной функции "init".
            rcmail.addEventListener('init', function(evt) {
                    /**
                    * Пользовательские команды должны быть зарегистрированы вместе с функцией обратного вызова,
                    * которая должна выполняться, если команда запускается.
                    * Третий аргумент активирует команду сразу после регистрации.
                    * Пример: rcmail.register_command(команда, функция_обработчик, включить)
                    * Примечание: одна команда - один обработчик
                    */
                    // регистрация комманды которую выполняет кнопка (объявление функции наверху)
                    //rcmail.register_command('plugin.btn_cmd_msg_request', msg_request, true, rcmail.env.uid);
                    //rcmail.register_command('plugin.btn_cmd_msg_request', msg_request, true);
                    // Эта кнопка запускает функцию сохранения настроек в хранилище "prefs".
                    rcmail.register_command('plugin.btn_cmd_toolbar', msg_save_prefs, true);
                    // условие срабатывает если сформировался список сообщеений
                    if (rcmail.message_list) {
                        // просдушиватель событий срабатывает если сообщеение выделено
                        rcmail.message_list.addEventListener('select', function(list) {
                                // включаем командную кнопку если выделено больше одного сообщения в списке
                                //rcmail.enable_command('plugin.btn_cmd_msg_request', list.get_selection(false).length > 1);
                                rcmail.enable_command('plugin.btn_cmd_toolbar');
                            }
                        );
                    }
                    // Условие проверки: есть-ли наши параметры настроек в хранилище "prefs".
                    if (rcmail.env.rm_duplicate_messages_uids) {
                        // Если настройки есть - запускаем функцию поиска дубликатов писем.
                        // Передаём POST-запрос на сервер с указанием выполнить функцию обработки писем -
                        // "msg_request": вызываем метод "http_post" объекта "rcmail" без параметров.
                        rcmail.http_post('plugin.msg_request');
                    }
                }
            );
            // Прослушиватель события работы функции "msg_save_prefs" о завершении сохранения настроек:
            // Функция "msg_save_prefs" отправляется ($this->rc->output->command('plugin.confirm_msg_save_prefs'))
            // и $this->rc->output->send() - команду запуска функции "confirm_msg_save_prefs" в браузере.
            rcmail.addEventListener('plugin.confirm_msg_save_prefs', function (evt) {
                    // Получаем локализованную метку. Настройки сохранены. Начинаем поиск дубликатов.
                    var msg = rcmail.get_label('rm_duplicate_messages.lbl26');
                    // Выводим уведомление о завершении работы нашей функции "msg_save_prefs"
                    // о завершении сохранения настроек: в первом параметре получаем локализованную метку,
                    // во втором указываем тип выводимого сообщения.
                    rcmail.display_message(msg, 'confirmation');
                    // Запускаем функцию "msg_request" которая будет обмениваться с сервером для поиска
                    // дубликатов писем.
                    msg_request();
                }
            );
            // Функция-прослушиватель события работы функции "msg_save_prefs" об удалении ранее сохранённых
            // пользовательских настроек поиска писем в массиве "prefs".
            // Функция "msg_save_prefs" отправляет ($this->rc->output->command('plugin.confirm_msg_save_prefs_remove'))
            // и $this->rc->output->send() - команду запуска функции "confirm_msg_save_prefs_remove" в браузере.
            rcmail.addEventListener('plugin.confirm_msg_save_prefs_remove', function (evt) {
                    // Получаем локализованную метку. "Настройки сохранены. Начинаем поиск дубликатов."
                    var msg = rcmail.get_label('rm_duplicate_messages.lbl28');
                    // Выводим уведомление о завершении работы нашей функции "msg_save_prefs"
                    // о завершении сохранения настроек: в первом параметре получаем локализованную метку,
                    // во втором указываем тип выводимого сообщения.
                    rcmail.display_message(msg, 'confirmation');
                }
            );
            // Функция-прослушиватель события работы функции "msg_request" о поиске и обработке дубликатов сообщений.
            // Функция "msg_request" отправляет ($this->rc->output->command('plugin.restart_msg_request'))
            // и $this->rc->output->send() - команду запуска функции 'restart_msg_request' в браузере.
            rcmail.addEventListener('plugin.restart_msg_request', function (evt) {
                    // Включаем блокировку интерфейса: выводим сообщение о работе процедуры.
                    // Параметр "lock" для того чтобы это сообщение перекрывалось следующим сообщением
                    // о выполняемых процедурах.
                    // Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
                    //   Первый параметр - "true" означает показывать сообщение или нет,
                    //   второй параметр - "plugin_name.label" получает локализованную метку из массива
                    // языковых настроек.
                    //var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl29');
                    // Циклически от браузера отправляется POST-запрос на сервер, от сервера приходит команда браузеру
                    // опять отправить запрос на сервер выполнить команду.
                    // Отправка команды на сервер для фоновой обработки писем:
                    // Передаём POST-запрос на сервер с указанием выполнить функцию обработки писем - "msg_request":
                    // вызываем метод "http_post" объекта "rcmail" без параметров.
                    rcmail.http_post('plugin.msg_request');
                    // JavaScript-метод "location.reload()" перезагружает текущую вкладку браузера и действует также
                    // как кнопка «Обновить страницу» в браузере.
                    // Выполняем цикличное обновление страницы с задержкой 60 секунд.
                    setTimeout(function(){
                            location.reload();
                        }, 60000);
                }
            );
            // Функция уведомления об окончании проверки на дубликаты и включения командной кнопки.
            rcmail.addEventListener('plugin.successful', function () {
                    // получим значение переменной от сервера
                    // поместим в переменную msg_marked колличество отмеченных сообщений
                    //var msg_marked = rcmail.env.msg_marked,
                    // Получим локализованные метки.
                    msg_successful = rcmail.get_label('rm_duplicate_messages.successful'),
                    // В переменную msg поместим полное сообщение которое нужно вывести.
                    msg = msg_successful;
                    // Выводим уведомление о завершении работы нашей функции - msg_request обработки сообщений.
                    // В первом параметре получаем локализованную метку, во втором указываем тип выводимого
                    // сообщения.
                    rcmail.display_message(msg, 'confirmation');
                    // Включим нашу коммандную кнопку.
                    window.rcmail.enable_command('plugin.btn_cmd_toolbar', true);
                    // Включаем блокировку интерфейса: выводим сообщение о работе процедуры.
                    // Параметр "lock" для того чтобы это сообщение перекрывалось следующим сообщением
                    // о выполняемых процедурах.
                    // Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
                    //   Первый параметр - "true" означает показывать сообщение или нет,
                    //   второй параметр - "plugin_name.label" получает локализованную метку из массива
                    // языковых настроек.
                    var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl27');
                    // Посылаем на сервер команду стереть данные пользовательских настроек текущего
                    // пользователя в хранилище.
                    // Передаём POST-запрос на сервер с указанием выполнить функцию сохранения настроек обработки
                    // писем - "msg_save_prefs": вызываем метод "http_post" объекта "rcmail" (параметры через
                    // запятую), метод "selection_post_data()" отправляет данные на сервер в массив [_POST] -
                    // там содержатся передаваемые параметры из браузера.
                    rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data(
                            {
                                // Передаём параметр указывающий функции "msg_save_prefs" удалить настройки
                                // поиска и обработки писем - в массиве пользовательских настроек "prefs".
                                //                                _user_prefs_null: "user_prefs_null"
                                _user_prefs_null_save_log: 'user_prefs_null_save_log'
                            }
                        ), lock);
                    // Обновим вид списка писем.
                    rcmail.refresh_list();
                }
            );
        }
    }
);
