// Функция сохраняет пользовательские настройки поиска и обработки писем текущего пользователя
// в хранилище пользовательских настроек - массив 'prefs'.
function msg_save_prefs() {
    // Переменные диалогового окна. Получаем надписи на локализованном языке из файла локализации.
    // Заголовок контента диалогового окна.
    var content = '<H3 align="center">'+rcmail.get_label('rm_duplicate_messages.lbl4')+'</H4><br/><div class="msg_save_prefs">'+
    // Первая рамка. Колличество обрабатываемых сообщений.
    '<div><fieldset><legend>'+rcmail.get_label('rm_duplicate_messages.lbl7')+'</legend>'+
    '<div class="processing_mode">'+
    '<input id="msg_sum_id0" name="msg_sum" type="radio" value="mssg_all" checked="checked">'+rcmail.get_label('rm_duplicate_messages.lbl8')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl9')+'</div>'+
    '<div class="processing_mode">'+
    // Переключатель показывает изменённый текст.
    '<input id="msg_sum_id1" name="msg_sum" type="radio" onChange="innerdiv();" value="mssg_seleted" />'+rcmail.get_label('rm_duplicate_messages.lbl10')+'</div>'+
    '<div class="processing_mode_lbl">'+
    '<div id="innerdiv" class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl11')+'</div>'+
    '</div></fieldset></div>'+
    // Вторая рамка. Режим обработки найденных дубликатов писем.
    '<div><fieldset><legend>'+rcmail.get_label('rm_duplicate_messages.lbl13')+'</legend>'+
    '<div class="processing_mode"><input name="msg_process_mode" type="radio" value="mark" checked="checked">'+
    rcmail.get_label('rm_duplicate_messages.lbl14')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl15')+'</div>'+
    '<div class="processing_mode"><input name="msg_process_mode" type="radio" value="delete">'+
    rcmail.get_label('rm_duplicate_messages.lbl16')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl17')+'<br /></div></fieldset></div>'+
    // Третья рамка. Режим работы плагина.
    '<div><fieldset><legend>'+rcmail.get_label('rm_duplicate_messages.lbl18')+'</legend>'+
    '<div class="processing_mode"><input name="plg_process_mode" type="radio" value="in_browser" checked="checked">'+
    rcmail.get_label('rm_duplicate_messages.lbl19')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl20')+'</div>'+
    '<div class="processing_mode"><input name="plg_process_mode" type="radio" value="in_server">'+
    rcmail.get_label('rm_duplicate_messages.lbl21')+'</div>'+
    '<div class="processing_mode_lbl">'+rcmail.get_label('rm_duplicate_messages.lbl22')+'</div></fieldset></div></div>';

    // Заголовок диалогового окна.
    var title = rcmail.get_label('rm_duplicate_messages.lbl3');
    // Кнопки диалогового окна.
    buttons={
    };
    // Кнопка 'Запустить'.
    buttons[rcmail.get_label('rm_duplicate_messages.lbl5')] = function(e) {
        // Получаем значения полей всплывающего окна с применением jQuery:
        // Колличество обрабатываемых сообщений: все сообщения, выделенные.
        var msg_sum = $('input[name="msg_sum"]:checked').val();
        // Режим обработки найденных дубликатов писем: отмечать, удалять.
        var msg_process_mode = $('input[name="msg_process_mode"]:checked').val();
        // Режим работы плагина: через браузер, серверный вариант.
        var plg_process_mode = $('input[name="plg_process_mode"]:checked').val();
        // Закрываем диалоговое окно.
        $(this).remove();
        // Включаем блокировку интерфейса: выводим сообщение о работе процедуры.
        // Параметр 'lock' для того чтобы это сообщение перекрывалось следующим сообщением
        // о выполняемых процедурах.
        var lock = rcmail.set_busy(true, 'rm_duplicate_messages.checkdpl');
        // В условии проверим - сколько 'uids' нужно отправить в запросе: все или только выделенные.
        if (msg_sum=='mssg_all') {
            // Получаем значения 'uids' всего списка писем:
            // Объявляем пустой массив для записи  'uids'.
            // Оператор 'var' объявит массив для всей функции - он будет виден за пределами цикла.
            var uids = [];
            // В цикле получаем 'uid' из объекта 'rcmail.message_list.rows'.
            for(uid in rcmail.message_list.rows){
                // Запишем каждое значение 'uids' в массив 'uids'.
                // Функция push - Добавляет элемент в конец массива.
                uids.push(uid);
            }
            }else{
            // Получаем значения 'uids' выделенного письма в списке.
            var uids = rcmail.message_list.selection;
        }
        // Остановим работу функции и выведем сообщение если значение 'uids' не получено.
        if (!uids) return window.alert('\n'+rcmail.get_label('rm_duplicate_messages.lbl23')+'\n'+'\n'+rcmail.get_label('rm_duplicate_messages.lbl24'));
        // Передаём запрос на сервер с указанием выполнить PHP-функцию сохранения настроек обработки писем - 'msg_save_prefs':
        // вызываем метод 'http_post' объекта 'rcmail' (параметры через запятую),
        // метод 'selection_post_data()' отправляет данные на сервер в массив [_POST] -
        // там содержатся передаваемые параметры из браузера.
        rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data(
                {
                    // Идентификаторы сообщений.
                    _uid: uids,
                    // Колличество обрабатываемых сообщений: все сообщения, выделенные.
                    _msg_sum: msg_sum,
                    // Режим обработки найденных дубликатов писем: отмечать, удалять.
                    _msg_process_mode: msg_process_mode,
                    // Режим работы плагина: через браузер, серверный вариант.
                    _plg_process_mode: plg_process_mode
                }
            ), lock);

        // Отключим нашу коммандную кнопку
        //window.rcmail.enable_command('plugin.btn_cmd_rm_dublecates', false);
    };
    // Кнопка 'Сбросить настройки'
    buttons[rcmail.get_label('rm_duplicate_messages.lbl6')] = function(e) {
        // Закрываем окно
        $(this).remove();
        // Посылаем на сервер команду стереть данные пользовательских настроек текущего пользователя в хранилище.
        var lock = rcmail.set_busy(true, 'rm_duplicate_messages.checkdpl');
        //rcmail.http_post('plugin.msg_save_prefs_remove', rcmail.selection_post_data(
        rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data(
                {
                	// Передаём параметр сброса настроек.
                	_user_prefs_null: 'user_prefs_null'
                }
            ), lock);
        //var lock = rcmail.set_busy(true, 'rm_duplicate_messages.checkdpl');
        //rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data({}), lock);
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
        // Если выделены - ставим переключатель на 'Выделенные'.
        document.getElementById('msg_sum_id1').checked = true;
    }
    var stop=1;
}
// Функция вставляет новый текст при переключении переключателя.
function innerdiv(){
    // Вставим новый текст.
    var innerdiv=document.getElementById('innerdiv').innerHTML=rcmail.get_label('rm_duplicate_messages.lbl12');
}
// Отправка команды на сервер для фоновой обработки писем.
function msg_handle(){
    // Получим содержимое по заданному адресу URL с помощью XMLHttpRequest.
    var req = new XMLHttpRequest(); // Создадим новый запрос.
    // Полyчим содержимое по заданномy адресy URL с помощью XMLHttpRequest.
    // Метод XMLHttpRequest.open() инициализирует новый запрос или повторно инициализирует уже созданный.
    // Синтаксис:     XMLHttpRequest.open('method', url[, async[, user[, password]]]).
    // Передаваемые параметры: ?param1=value1&param2=value2&param3=value3
    req.open('GET', 'http://localhost/rc147/plugins/rm_duplicate_messages/msg_handle.php');    // Откроем запрос.
    // Метод XMLHttpRequest.send() отправляет запрос.
    // Если запрос асинхронный (каким он является по-умолчанию), то возврат из данного метода происходит
    // сразу после отправления запроса. Если запрос синхронный, то метод возвращает управление только после получения ответа.
    // Метод send() принимает необязательные аргументы в тело запросов. Если метод запроса GET или HEAD,
    // то аргументы игнорируются и тело запроса устанавливается в null.
    // После отправки в консоле появляются заголовки.
    req.send(null);    // Отправим запрос.
}
// функция поиска дубликатов сообщений в переданном массиве. Функция разбора массива.
function msg_compare(){
    var msgs=JSON.parse(localStorage.msgs_json);
    var a;
}

// Инициализируем объект 'rcmail: rcube_webmail'. ($(document) взято из jQuery.)
$(document).ready(function() {
        // если инициализирован объект 'window.rcmail' выполняем операторы в условии
        if (window.rcmail) {
            /**
            * Добавление и регистрация слушателей событий
            * Это делается с помощью следующих двух функций:
            *     rcmail.addEventListener|removeEventListener('event', callback);
            * Функция callback получает объект события в качестве одного аргумента.
            * Этот объект события содержит свойства, специфичные для события.
            * Они перечислены в качестве аргументов под соответствующим описанием события.
            *
            * EventTargetМетод addEventListener()устанавливает функцию, которая будет вызываться всякий раз,
            * когда будет происходить указанное событие. Общими целями являются Element, Document, и Window,
            * но целью может быть любой объект, поддерживающий события (например XMLHttpRequest).
            *      addEventListener() работает путем добавления функции или объекта, реализующего EventListener
            * в список прослушивателей событий для указанного типа события на том EventTarget, на котором оно вызывается.
            * https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
            */
            // параметр 'init' - событие, параметр 'function(evt)' это - ананимная callback-функция
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
                    // Эта кнопка запускает функцию сохранения настроек в хранилище 'prefs'.
                    rcmail.register_command('plugin.btn_cmd_toolbar', msg_save_prefs, true);
                    // условие срабатывает если сформировался список сообщеений
                    if (rcmail.message_list) {
                        // просдушиватель событий срабатывает если сообщеение выделено
                        rcmail.message_list.addEventListener('select', function(list) {
                                // включаем командную кнопку если выделено больше одного сообщения в списке
                                //rcmail.enable_command('plugin.btn_cmd_msg_request', list.get_selection(false).length > 1);
                                rcmail.enable_command('plugin.btn_cmd_toolbar');
                                //localStorage.uids=list.selection;
                            }
                        );
                    }
                }
            );

            // функция получения массива дубликатов сообщений от сервера.
            rcmail.addEventListener('plugin.get_msg', function () {
                    // Получаем значение переменной от сервера и поместим в переменную 'msgs' колличество отмеченных сообщений.
                    var msgs_json = rcmail.env.msgs_json,
                    // Получаем локализованную метку.
                    //lbl_msg_compare = rcmail.get_label('rm_duplicate_messages.lbl_msg_compare');
                    lbl_get_msg = rcmail.set_busy(true, 'rm_duplicate_messages.lbl_get_msg');
                    // в переменную msg поместим полное сообщение которое нужно вывести
                    //msg = msg_successful + msg_marked;
                    // выводим уведомление о завершении работы нашей функции - msg_request обработки сообщений
                    // в первом параметре получаем локализованную метку, во втором указываем тип выводимого сообщения
                    //rcmail.display_message(lbl_msg_compare, 'loading');
                    rcmail.display_message(lbl_get_msg, 'loading');
                    // Если браузер поддерживает локальное хранилище выполним сохранение - запишем значения наших переменных в локальное хранилище.
                    if (window.localStorage) {
                        // Запишем текущие значения наших переменных в хранилище.
                        localStorage.msgs_json = msgs_json;
                        sessionStorage.msgs_json = msgs_json;
                    }
                    // обновим вид списка писем
                    rcmail.refresh_list();
                    // Вызываем функцию обработки массива полученных сообщений.
                    //msg_compare();
                }
            );

            rcmail.addEventListener('refreshing', function () {
                    window.alert(refreshing);
                });

            // функция уведомления об окончании проверки на дубликаты и включения кнопки
            rcmail.addEventListener('plugin.successful', function () {
                    // включим нашу коммандную кнопку
                    window.rcmail.enable_command('plugin.btn_cmd_toolbar', true);
                    // получим значение переменной от сервера
                    // поместим в переменную msg_marked колличество отмеченных сообщений
                    var msg_marked = rcmail.env.msg_marked,
                    // получим локализованные метки
                    msg_successful = rcmail.get_lbl('rm_duplicate_messages.successful'),
                    // в переменную msg поместим полное сообщение которое нужно вывести
                    msg = msg_successful + msg_marked;
                    // выводим уведомление о завершении работы нашей функции - msg_request обработки сообщений
                    // в первом параметре получаем локализованную метку, во втором указываем тип выводимого сообщения
                    rcmail.display_message(msg, 'confirmation');
                    // обновим вид списка писем
                    rcmail.refresh_list();
                }
            );
        }
    }
);
