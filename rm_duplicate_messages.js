// Ищем и выделяем строки с одинаковыми письмами.
function select_stringsEmails(){
	// Оператор "var" объявит массив для всей функции - он будет виден
	// за пределами цикла.
	// Создаём пустой ассоциативный массив-объект.
	var stringEmails={
	};
	// Создаём пустой вспомогательный массив для записи одинаковых
	// строк писем.
	var stringEmails1=[];
	// В цикле перебираем коллекцию строк писем на странице.
	// Результат записываем в переменную-объект "stringEmail".
	for (var stringEmail of rcmail.message_list.list.rows) {
		// Переменной "strEmail" присвоим строку контента из коллекции
		// с письмами.
		var strEmail=stringEmail.outerText;
		// Переменной "idEmail" присвоим уникальный id строки из коллекции
		// с письмами на странице.
		var idEmail=stringEmail.id;
		// Если переменная "idEmail" пустая - начнём новую интерацию цикла.
		if(idEmail=="") continue;
		// Сделаем замену табуляции на пробел, удалим одинарные и
		// двойные кавычки.
		var strEmail = strEmail.replace(/\s+/g, ' ');
		strEmail = strEmail.replace(/"/g, '');
		strEmail = strEmail.replace(/'/g, '');
		// Удалим начальные и концевые пробелы. Используем метод trim().
		strEmail = strEmail.trim();
		// Запишем в ассоциативный массив-объект "stringEmails" id строки
		// письма (переменная "idEmail") и запись строки письма
		// (переменная "strEmail").
		stringEmails[idEmail] = strEmail;
		// Запишем каждое значение "strEmail" в вспомогательный массив
		// "stringEmails1" для выделения дублирующихся записей:
		// методом push добавим элемент в конец массива.
		stringEmails1.push(strEmail);
	}
	// Удалим из массива "stringEmails1" уникальные значения элементов.
	// Оставим только вовторяющиеся значения в том же порядке.
	var uniqStringEmails = stringEmails1.filter(function(number, index, stringEmails1) {
			return (!(stringEmails1.lastIndexOf(number) == index) || !(stringEmails1.indexOf(number) == index)) && true;
		});
	// Удалим из массива "uniqStringEmails" вовторяющиеся значения элементов.
	// Оставим только уникальные значения в том же порядке.
	var uniqStringEmails = [...new Set(uniqStringEmails)];
	// Создадим переменные "element", "trId" для записи найденного
	// элемента по id.
	var element, trId;
	// Перебираем поля объекта "stringEmails".
	// Получаем ключ: переменная "key" - имя свойства объекта "stringEmails"
	// (это id элемента на странице),
	// и значение свойства - объект "stringEmails[key]" (это значение
	// текущего id объекта на странице).
	for (var key in stringEmails) {
		// Выполним поиск значений элементов массива "uniqStringEmails" в
		// массиве "stringEmails".
		// Перебираем значения элементов двух массивов и сравниваем
		// значения элементов этох массивов.
		uniqStringEmails.forEach((str) => {
				// Ищем значения элементов массива "uniqStringEmails"
				// в объекте "stringEmails".
				// Сравним значения элементов массива "uniqStringEmails"
				// со значением свойст объекта "stringEmails".
				if(stringEmails[key]==str){
					// Если найденная строка равна значению переменой "str"
					// то переменная "key" содержит id элемента на
					// странице со списком писем.
					// Ищем текущий id элемента на странице: присвоим
					// переменной "element" найденный элемент.
					element = document.getElementById(key);
					// Добавим стиль "selected" (стиль выделенной строки)
					// к найденному элементу.
					// Метод classList.add() позволяет добавить один или
					// несколько классов к элементу, не затрагивая
					// существующие.
					element.classList.add("selected");
					// Добавим атрибут "aria-selected="true"" для элемента
					// "tr" на странице.
					// В JavaScript для добавления атрибута элементу
					// используется метод setAttribute() интерфейса Element.
					// Он позволяет указать имя атрибута и его значение.
					element.setAttribute("aria-selected", "true");
					// Запишем значение "uid" текущего элемента
					// в массив "rcmail.message_list.selection":
					// методом push добавим элемент в конец массива.
					rcmail.message_list.selection.push(element.uid);
				}
			})
	}
	// Для включения кнопок управления письмами: нужно выделить одну строку с письмом.
	// Удалим из массива "rcmail.message_list.selection" вовторяющиеся значения элементов.
	// Оставим только уникальные значения в том же порядке.
	rcmail.message_list.selection = [...new Set(rcmail.message_list.selection)];
}

// Функция поиска дубликатов: отправляем запрос на сервер.
function msg_search_duplicates(msg_processing,msg_process_mode,plg_process_mode){
	// Условие проверки - сколько "uids" нужно отправить в POST-запросе:
	// все какие есть в текущей папке, все на текущей странице,
	// только выделенные на текущей странице.
	if (msg_processing=='msg_all') {
		// Отправляем все какие есть в текущей папке.
		// Значения "uids" возмём в скрипте PHP.
		var uids = 'msg_all';
	}else if(msg_processing=='msg_current_page'){
		// Отправляем все на текущей странице.
		// Объявляем пустой массив для записи  "uids".
		// Оператор "var" объявит массив для всей функции - он будет виден
		// за пределами цикла.
		var uids = [];
		// Получаем значения "uids" списка писем текущей страницы согласно
		// настройкам Roundcube файл - config.inc.php,
		// параметр - $config['mail_pagesize'].
		// В цикле получаем "uid" из объекта "rcmail.message_list.rows".
		for(uid in rcmail.message_list.rows){
			// Запишем каждое значение "uids" в массив "uids".
			// Функция push - добавляет элемент в конец массива.
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
		return window.alert('\n' +rcmail.get_label('rm_duplicate_messages.lbl23')+ '\n'+'\n' +rcmail.get_label('rm_duplicate_messages.lbl24'));
		// Остановим работу функции.
		exit;
	}
	// Включаем блокировку интерфейса: выводим сообщение о работе процедуры.
	// Параметр "lock" для того чтобы это сообщение перекрывалось следующим
	// сообщением о выполняемых процедурах.
	// Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
	// Первый параметр - "true" означает показывать сообщение или нет,
	// второй параметр - "plugin_name.label" получает локализованную метку
	// из массива языковых настроек.
	var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl25');
	// Передаём POST-запрос на сервер с указанием выполнить функцию
	// сохранения настроек обработки писем - "msg_save_prefs": вызываем
	// метод "http_post" объекта "rcmail" (параметры через запятую), метод
	// "selection_post_data()" отправляет данные на сервер в массив [_POST]
	// который содержит передаваемые параметры из браузера.
	rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data(
			{
				// Идентификаторы писем.
				_uid: uids,
				// Порядок обрабатываемых писем:
				// все сообщения или только выделенные.
				_msg_processing: msg_processing,
				// Режим обработки найденных дубликатов писем: отмечать, удалять.
				_msg_process_mode: msg_process_mode,
				// Режим работы плагина: через браузер, серверный вариант.
				_plg_process_mode: plg_process_mode
			}
		), lock);
	// Отключим нашу коммандную кнопку
	window.rcmail.enable_command('plugin.btn_cmd_rm_dublecates', false);
	// Закрываем диалоговое окно.
	$(this).remove();
}

// Сохраняем пользовательские настройки поиска и обработки писем текущего
// пользователя в массив "prefs" (хранилище пользовательских настроек).
function msg_save_prefs() {
	// Переменные диалогового окна.
	// Получаем надписи на локализованном языке из файла локализации.
	// Заголовок контента диалогового окна.
	var content = '<H3 align="center">' +rcmail.get_label('rm_duplicate_messages.lbl4')+ '</H4><br/><div class="msg_save_prefs">'+
	// Первая рамка. Колличество обрабатываемых писем.
	'<div><fieldset><legend>' +rcmail.get_label('rm_duplicate_messages.lbl7')+ '</legend>'+
	'<div class="processing_mode">'+
	'<input id="msg_processing_id0" name="msg_processing" type="radio" value="msg_all" checked="checked">' +rcmail.get_label('rm_duplicate_messages.lbl8')+ '</div>'+
	'<div class="processing_mode_lbl">' +rcmail.get_label('rm_duplicate_messages.lbl9')+ '</div>'+
	'<div class="processing_mode">'+
	// Переключатель показывает изменённый текст.
	'<input id="msg_processing_id1" name="msg_processing" type="radio" onChange="innerdiv();" value="msg_selected" />' +rcmail.get_label('rm_duplicate_messages.lbl10')+ '</div>'+
	'<div class="processing_mode_lbl">'+
	'<div id="innerdiv" class="processing_mode_lbl">' +rcmail.get_label('rm_duplicate_messages.lbl11')+ '</div>'+
	'<div class="selectedmsg">' +rcmail.get_label('rm_duplicate_messages.lbl32') +rcmail.message_list.selection.length+ '</div>'+
	'</div></fieldset></div>'+
	// Вторая рамка. Режим обработки найденных дубликатов писем.
	'<div><fieldset><legend>' +rcmail.get_label('rm_duplicate_messages.lbl13')+ '</legend>'+
	'<div class="processing_mode"><input name="msg_process_mode" type="radio" value="mark">'+
	rcmail.get_label('rm_duplicate_messages.lbl14')+ '</div>'+
	'<div class="processing_mode_lbl">' +rcmail.get_label('rm_duplicate_messages.lbl15')+ '</div>'+
	'<div class="processing_mode"><input name="msg_process_mode" type="radio" value="del" checked="checked">'+
	rcmail.get_label('rm_duplicate_messages.lbl16')+ '</div>'+
	'<div class="processing_mode_lbl">' +rcmail.get_label('rm_duplicate_messages.lbl17')+ '<br /></div></fieldset></div>'+
	// Третья рамка. Режим работы плагина.
	//'<div><fieldset><legend>' +rcmail.get_label('rm_duplicate_messages.lbl18')+ '</legend>'+
	//'<div class="processing_mode"><input name="plg_process_mode" type="radio" value="in_browser" checked="checked">'+
	//rcmail.get_label('rm_duplicate_messages.lbl19')+ '</div>'+
	//'<div class="processing_mode_lbl">' +rcmail.get_label('rm_duplicate_messages.lbl20')+ '</div>'+
	//'<div class="processing_mode"><input disabled name="plg_process_mode" type="radio" value="in_server">'+
	//rcmail.get_label('rm_duplicate_messages.lbl21')+ '</div>'+
	//'<div class="processing_mode_lbl">' +rcmail.get_label('rm_duplicate_messages.lbl22')+ '</div>
	'</fieldset></div></div>';
	// Заголовок диалогового окна.
	var title = rcmail.get_label('rm_duplicate_messages.lbl3');
	// Кнопки диалогового окна.
	buttons={
	};
	// Кнопка "Выделить дубликаты писем".
	buttons[rcmail.get_label('rm_duplicate_messages.lbl31')] = function(e) {
		// Закрываем диалоговое окно "Установка параметров".
		$(this).remove();
		// Вызываем функцию выделения строк с дублирующимися письмами.
		select_stringsEmails();
	}
	// Кнопка "Запустить удаление дубликатов писем".
	buttons[rcmail.get_label('rm_duplicate_messages.lbl5')] = function(e) {
		// Получаем значения полей всплывающего окна с применением jQuery:
		// Порядок обрабатываемых писем:
		// все сообщения или только выделенные.
		var msg_processing = $('input[name="msg_processing"]:checked').val();
		// Режим обработки найденных дубликатов писем: отмечать, удалять.
		var msg_process_mode = $('input[name="msg_process_mode"]:checked').val();
		// Режим работы плагина: через браузер, серверный вариант.
		var plg_process_mode = $('input[name="plg_process_mode"]:checked').val();
		// Закрываем диалоговое окно "Установка параметров".
		$(this).remove();
		// Вызываем функцию поиска дубликатов.
		msg_search_duplicates(msg_processing,msg_process_mode,plg_process_mode);
	};
	// Кнопка "Сбросить настройки"
	buttons[rcmail.get_label('rm_duplicate_messages.lbl6')] = function(e) {
		// Закрываем диалоговое окно "Установка параметров".
		$(this).remove();
		// Включаем блокировку интерфейса: выводим сообщение о работе процедуры.
		// Параметр "lock" для того чтобы это сообщение перекрывалось следующим
		// сообщением о выполняемых процедурах.
		// Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
		// Первый параметр - "true" означает показывать сообщение или нет,
		// второй параметр - "plugin_name.label" получает локализованную метку
		// из массива языковых настроек.
		var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl27');
		// Посылаем на сервер команду стереть данные пользовательских настроек
		// текущего пользователя в массиве "prefs" (хранилище пользовательских
		// настроек).
		// Передаём POST-запрос на сервер с указанием выполнить функцию
		// сохранения настроек обработки писем - "msg_save_prefs": вызываем
		// метод "http_post" объекта "rcmail" (параметры через запятую),
		// метод "selection_post_data()" отправляет данные на сервер в массив
		// [_POST] - там содержатся передаваемые параметры из браузера.
		rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data(
				{
					// Передаём параметр указывающий функции "msg_save_prefs" удалить
					// настройки поиска и обработки писем в массиве "prefs"
					// (хранилище пользовательских настроек).
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
	// В услови проверяем выделены ли письма в списке.
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
	// Параметр "lock" для того чтобы это сообщение перекрывалось следующим
	// сообщением о выполняемых процедурах.
	// Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
	//   Первый параметр - "true" означает показывать сообщение или нет,
	//   второй параметр - "plugin_name.label" получает локализованную метку
	// из массива языковых настроек.
	var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl29');
	// Передаём POST-запрос на сервер с указанием выполнить функцию
	// сохранения настроек обработки писем - "msg_save_prefs": вызываем метод
	// "http_post" объекта "rcmail" (параметры через запятую), метод
	// "selection_post_data()" отправляет данные на сервер в массив "POST" -
	// там содержатся передаваемые параметры из браузера.
	rcmail.http_post('plugin.msg_request', rcmail.selection_post_data({}), lock);
}

// Инициализируем объект "rcmail: rcube_webmail". ($(document) взято из jQuery.)
$(document).ready(function() {
		// Если инициализирован объект "window.rcmail" выполняем операторы в
		// условии.
		if (window.rcmail) {
			/**
			* Добавление и регистрация слушателей событий:
			* Это делается с помощью следующих двух функций:
			*     rcmail.addEventListener|removeEventListener('event', callback);
			* Функция callback получает объект события в качестве одного аргумента.
			* Этот объект события содержит свойства, специфичные для события.
			* Они перечислены в качестве аргументов под соответствующим описанием
			* события.
			*   Метод addEventListener()устанавливает функцию, которая будет
			* вызываться всякий раз, когда будет происходить указанное событие.
			* Общими целями являются Element, Document, и Window.
			* Целью может быть любой объект, поддерживающий события например:
			* XMLHttpRequest или объект формы.
			*   addEventListener() работает путем добавления функции или объекта,
			* реализующего EventListener в список прослушивателей событий для
			* указанного типа события на том EventTarget, на котором оно вызывается.
			* https://developer.mozilla.org/en-US/docs/Web/API/EventTarget/addEventListener
			* Параметр "init" - событие, параметр "function(evt)" это - ананимная
			* callback-функция.
			*/
			// Прослушиватель события работы системной функции "init".
			rcmail.addEventListener('init', function(evt) {
					/**
					* Пользовательские команды (первый аргумент) должны быть
					* зарегистрированы вместе с функцией обратного вызова (второй
					* аргумент), которая должна выполняться, если команда запускается.
					* Третий аргумент активирует команду сразу после регистрации.
					* Пример:
					*   rcmail.register_command(команда, функция_обработчик, включить)
					* Примечание: одна команда - один обработчик
					*/
					// Регистрация комманды которую выполняет кнопка (объявление
					// функции наверху).
					//rcmail.register_command('plugin.btn_cmd_msg_request', msg_request, true, rcmail.env.uid);
					//rcmail.register_command('plugin.btn_cmd_msg_request', msg_request, true);
					// Эта кнопка запускает функцию сохранения настроек в массиве
					// "prefs" (хранилище пользовательских настроек).
					rcmail.register_command('plugin.btn_cmd_toolbar', msg_save_prefs, true);
					// Условие срабатывает если сформировался список сообщеений.
					if (rcmail.message_list) {
						// Просдушиватель событий срабатывает если сообщеение выделено.
						rcmail.message_list.addEventListener('select', function(list) {
								// Включаем командную кнопку если выделено больше одного
								// сообщения в списке.
								//rcmail.enable_command('plugin.btn_cmd_msg_request', list.get_selection(false).length > 1);
								rcmail.enable_command('plugin.btn_cmd_toolbar');
							}
						);
					}
					// Условие проверки: есть ли наши параметры настроек в массиве
					// "prefs" (хранилище пользовательских настроек).
					if (rcmail.env.rm_duplicate_messages_uids) {
						// Если настройки есть - запускаем функцию поиска дубликатов
						// писем. Передаём POST-запрос на сервер с указанием выполнить
						// функцию обработки писем - "msg_request": вызываем метод
						// "http_post" объекта "rcmail" без параметров.
						rcmail.http_post('plugin.msg_request');
					}
				}
			);
			// Прослушиватель события работы функции "msg_save_prefs" о завершении
			// сохранения настроек: Функция "msg_save_prefs" отправляется так
			//  ($this->rc->output->command('plugin.confirm_msg_save_prefs'))
			// и $this->rc->output->send() - команду запуска функции "confirm_msg_save_prefs" в браузере.
			rcmail.addEventListener('plugin.confirm_msg_save_prefs', function (evt) {
					// Получаем локализованную метку - настройки сохранены.
					// Начинаем поиск дубликатов.
					var msg = rcmail.get_label('rm_duplicate_messages.lbl26');
					// Выводим уведомление о завершении работы нашей функции
					// "msg_save_prefs" о завершении сохранения настроек: в первом
					// параметре получаем локализованную метку, во втором
					// указываем тип выводимого сообщения.
					rcmail.display_message(msg, 'confirmation');
					// Запускаем функцию "msg_request" которая будет обмениваться с
					// сервером в процессе поиска дубликатов писем.
					msg_request();
				}
			);
			// Функция-прослушиватель события работы функции "msg_save_prefs" об
			// удалении ранее сохранённых пользовательских настроек поиска писем
			// в массиве  "prefs" (хранилище пользовательских настроек).
			// Функция "msg_save_prefs" отправляет
			//  ($this->rc->output->command('plugin.confirm_msg_save_prefs_remove'))
			// и $this->rc->output->send() - команду запуска функции
			// "confirm_msg_save_prefs_remove" в браузере.
			rcmail.addEventListener('plugin.confirm_msg_save_prefs_remove', function (evt) {
					// Получаем локализованную метку - "Настройки сохранены.
					// Начинаем поиск дубликатов."
					var msg = rcmail.get_label('rm_duplicate_messages.lbl28');
					// Выводим уведомление о завершении работы нашей функции
					// "msg_save_prefs" о завершении сохранения настроек:
					// в первом параметре получаем локализованную метку,
					// во втором указываем тип выводимого сообщения.
					rcmail.display_message(msg, 'confirmation');
				}
			);
			// Функция-прослушиватель события работы функции "msg_request" о
			// поиске и обработке дубликатов писем.
			// Функция "msg_request" отправляет
			//  ($this->rc->output->command('plugin.restart_msg_request'))
			// и $this->rc->output->send() - команду запуска функции
			// 'restart_msg_request' в браузере.
			rcmail.addEventListener('plugin.restart_msg_request', function (evt) {
					// Включаем блокировку интерфейса:
					// выводим сообщение о работе процедуры.
					// Параметр "lock" для того чтобы это сообщение перекрывалось
					// следующим сообщением о выполняемых процедурах.
					// Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
					//  Первый параметр - "true" означает показывать сообщение или нет,
					//  второй параметр - "plugin_name.label" получает локализованную
					// метку из массива языковых настроек.
					//var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl29');
					// Циклически от браузера отправляется POST-запрос на сервер, от
					// сервера приходит команда браузеру опять отправить запрос на
					// сервер выполнить команду.
					// Отправка команды на сервер для фоновой обработки писем:
					// Передаём POST-запрос на сервер с указанием выполнить функцию
					// обработки писем - "msg_request":
					// вызываем метод "http_post" объекта "rcmail" без параметров.
					rcmail.http_post('plugin.msg_request');
					// JavaScript-метод "location.reload()" перезагружает текущую
					// вкладку браузера и действует также как кнопка «Обновить
					// страницу» в браузере.
					// Выполняем цикличное обновление страницы с задержкой 60 секунд.
					setTimeout(function(){
							location.reload();
						}, 60000);
				}
			);
			// Функция уведомления об окончании проверки на дубликаты и включения
			// командной кнопки.
			rcmail.addEventListener('plugin.successful', function () {
					// Получим значение переменной от сервера. Поместим в переменную
					// msg_marked колличество отмеченных писем
					//var msg_marked = rcmail.env.msg_marked,
					// Получим локализованные метки.
					msg_successful = rcmail.get_label('rm_duplicate_messages.successful'),
					// В переменную msg поместим полное сообщение которое нужно
					// вывести.
					msg = msg_successful;
					// Выводим уведомление о завершении работы нашей функции -
					// msg_request обработки писем.
					// В первом параметре получаем локализованную метку,
					// во втором указываем тип выводимого сообщения.
					rcmail.display_message(msg, 'confirmation');
					// Включим нашу коммандную кнопку.
					window.rcmail.enable_command('plugin.btn_cmd_toolbar', true);
					// Включаем блокировку интерфейса: выводим сообщение о работе
					// процедуры.
					// Параметр "lock" для того чтобы это сообщение перекрывалось
					// следующим сообщением о выполняемых процедурах.
					// Синтаксис: rcmail.set_busy(true, 'plugin_name.label').
					//   Первый параметр - "true" означает показывать сообщение или нет,
					//   второй параметр - "plugin_name.label" получает
					//   локализованную метку из массива языковых настроек.
					var lock = rcmail.set_busy(true, 'rm_duplicate_messages.lbl27');
					// Посылаем на сервер команду стереть данные пользовательских
					// настроек текущегопользователя в массиве "prefs"
					// (хранилище пользовательских настроек).
					// Передаём POST-запрос на сервер с указанием выполнить функцию
					// сохранения настроек обработки писем - "msg_save_prefs":
					// вызываем метод "http_post" объекта "rcmail" (параметры через
					// запятую), метод "selection_post_data()" отправляет данные на
					// сервер в массив [_POST] - там содержатся передаваемые
					// параметры из браузера.
					rcmail.http_post('plugin.msg_save_prefs', rcmail.selection_post_data(
							{
								// Передаём параметр указывающий функции "msg_save_prefs"
								// удалить настройки поиска и обработки писем - в массиве
								// "prefs" (хранилище пользовательских настроек).
								// _user_prefs_null: "user_prefs_null"
								_user_prefs_null_save_log: 'user_prefs_null_save_log'
							}
						), lock);
					// Обновим вид списка писем.
					rcmail.refresh_list();
				}
			);
		}
		// Запускаем поиск дубликатов писем по команде кнопки на клавиатуре.
		document.addEventListener('keydown', function(e) {
            // Если нажата кнопка "Escape" - вызываем функцию
            // "msg_search_duplicates" с параметрами.
				if (e.keyCode == 27){
					// Присвоим значения переменным
					// Порядок обрабатываемых писем: только выделенные.
					var msg_processing = "msg_selected";
					// Режим обработки найденных дубликатов писем: удалять.
					var msg_process_mode = "del"
					// Режим работы плагина: через браузер, серверный вариант.
					var plg_process_mode;
					// Вызываем функцию поиска дубликатов.
					msg_search_duplicates(msg_processing,msg_process_mode,plg_process_mode);
				}
				// Для выбора клавиши управления на клавиатуре.
				//console.log("Нажата клавиша: ", e.key);
				//console.log("Код клавиши: ", e.keyCode);
		});
	}
);
