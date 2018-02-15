<?php

namespace project;
//Как я понял, это проверка на то,что скрипт не запушен через shell, ну хотя кто его знает
//вторая догадка, возможно не существует массива $_GET если перейти по ссылки на страницу без параметров
if (!isset($_REQUEST)) {
    return;
}
//////////////////////////////////////////////////
/*
 * Подключение модулей
 */
include_once("setting.php");

//пока нет автолодера
include_once("request/iRequest.php");
include_once("request/TimeRequest.php");
include_once("request/HelpRequest.php");
include_once("request/ErrorRequest.php");
/////////////////////////////////////////////////
/// подключение классов
///
use project\request as request;

/////////////////////////////////////////////////

//$data = json_decode(file_get_contents('php://input'));
//не норма
$data = json_decode($_GET['data']);
// проверяем secretKey
if (strcmp($data->secret, SECRET_KEY) !== 0 && strcmp($data->type, 'confirmation') !== 0)
    return;

//Проверяем, что находится в поле "type"
switch ($data->type) {
    //Если это уведомление для подтверждения адреса сервера...
    case 'confirmation':
        //...отправляем строку для подтверждения адреса
        echo CONFIRMATION_TOKEN;
        break;

    //Если это уведомление о новом сообщении...
    case 'message_new':
	
		switch($data->object->body) 
		{
			case 'нужна помощь':
			case 'help me':
                $request = new request\HelpRequest($data);
				
			break;
			
			case 'время':
                $request = new request\TimeRequest($data);
			break;
			
			
			default:
                //затем с помощью users.get получаем данные об авторе
                $request = new request\ErrorRequest($data);
        }


        $get_params = http_build_query($request->getResult());

			file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);

		//Возвращаем "ok" серверу Callback API
		echo('ok');

    break;
}
?>