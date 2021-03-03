// функция запроса сообщений из базы.
function msg_request() {
    // Получим значение 'uids' выделенного элемента в списке элементов.
    //var uids = rcmail.message_list.get_selection();
    var uids = rcmail.message_list.selection;
    
    // Весь список писем.
    //var uids = rcmail.message_list.rows;
    
    // Остановим работу функции и выведем сообщение если значение 'uids' не получено.
    if (!uids) return window.alert('\n' + "Неудаётся получить uid сообщения." + '\n' + '\n' + "Перезагрузите страницу.");
    // Включаем блокировку интерфейса: выводим сообщение о работе процедуры запросы писем из базы.
    // Параметр 'lock' для того чтобы это сообщение перекрывалось следующим сообщением
    // о том что процедура запроса сообщений завершена.
    var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl_msg_request');
    // Передаём запрос на сервер выполнить PHP-функцию запроса сообщений - 'msg_request':
    // вызываем метод 'http_post' объекта 'rcmail' (параметры через запятую),
    // метод 'selection_post_data()' отправляет данные на сервер в массив [_POST] -
    // там содержатся идентификаторы сообщений.
    rcmail.http_post('plugin.msg_request', rcmail.selection_post_data({_uid: uids}), lock);
    
    // Отключаем нашу коммандную кнопку.
    //window.rcmail.enable_command('plugin.btn_cmd_msg_request', false);
}
// Отправка команды на сервер для фоновой обработки писем.
function msg_handle(){	
    // Получим содержимое по заданному адресу URL с помощью XMLHttpRequest.
    var req = new XMLHttpRequest(); // Создадим новый запрос.
    // Полyчим содержимое по заданномy адресy URL с помощью XMLHttpRequest.
    // Метод XMLHttpRequest.open() инициализирует новый запрос или повторно инициализирует уже созданный.
    // Синтаксис:     XMLHttpRequest.open('method', url[, async[, user[, password]]]).
    // Передаваемые параметры: ?param1=value1&param2=value2&param3=value3
    req.open('GET', 'http://localhost/rc147/plugins/rm_duplicate_messages/msg_handle.php');	// Откроем запрос.
    // Метод XMLHttpRequest.send() отправляет запрос.
    // Если запрос асинхронный (каким он является по-умолчанию), то возврат из данного метода происходит
    // сразу после отправления запроса. Если запрос синхронный, то метод возвращает управление только после получения ответа.
    // Метод send() принимает необязательные аргументы в тело запросов. Если метод запроса GET или HEAD,
    // то аргументы игнорируются и тело запроса устанавливается в null.
    // После отправки в консоле появляются заголовки.
    req.send(null);	// Отправим запрос.
}
// функция поиска дубликатов сообщений в переданном массиве. Функция разбора массива.
function msg_compare(){
    var msgs=JSON.parse(localStorage.msgs_json);
    var a;
}

// Инициализируем объект 'rcmail: rcube_webmail'.
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
                    rcmail.register_command('plugin.btn_cmd_msg_request', msg_request, true);
                    // условие срабатывает если сформировался список сообщеений
                    if (rcmail.message_list) {
                        // просдушиватель событий срабатывает если сообщеение выделено
                        rcmail.message_list.addEventListener('select', function(list) {
                                // включаем командную кнопку если выделено больше одного сообщения в списке
                                rcmail.enable_command('plugin.btn_cmd_msg_request', list.get_selection(false).length > 1);
                                localStorage.uids=list.selection;
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
                    window.rcmail.enable_command('plugin.btn_cmd_msg_request', true);
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
