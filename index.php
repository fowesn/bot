<?php

// isset() проверяет, установлена ли переменная отличным от NULL значением
// $_REQUEST - ассоциативный массив (суперглобальный), который содержит данные переменных $_GET, $_POST, $_COOKIE
// все это нужно здесь для того, чтобы позволить скрипту получить доступ к данным, которые используются в GET и POST запросах, а также к COOKIES
if (!isset($_REQUEST)) {
	    return;
}

// строка для подтверждения адреса сервера из настроек Callback API
$confirmationToken = '09476c9c';

// ключ доступа сообщества - для обращения к API от имени сообщества
$communityToken = '36ade04328cb1a2f61bdc53523cf5f17d79b6d1d76edfce7db02a01dc67a6f56c1a8b1cef34b8af77fa66';

// Secret key
$secretKey = 'kappa_tryout_key';

// версия vk api
$version = '5.71';

//Получаем и декодируем уведомление
 // WHAT IS GOING ON HERE ???
$data = json_decode(file_get_contents('php://input'));

// проверяем secretKey
if(strcmp($data->secret, $secretKey) !== 0 && strcmp($data->type, 'confirmation') !== 0)
    return;

//Проверяем, что находится в поле "type"
switch ($data->type) {
    //Если это уведомление для подтверждения адреса сервера...
    case 'confirmation':
        //...отправляем строку для подтверждения адреса
        echo $confirmationToken;
        break;

    //Если это уведомление о новом сообщении...
    case 'message_new':
	
		switch($data->object->body) 
		{
			case 'нужна помощь':
			case 'help me':
				$request_params = array(
					'message' => "Пока что я могу мало, но учусь!<br>".
									"Напиши \"время\", и я скажу тебе, который час",
					'user_id' => $data->object->user_id,
					'access_token' => $communityToken,
					'v' => $version
					);
				
			break;
			
			case 'время':
			$date = date("H:i:s");
				$request_params = array(
					'message' => "Сейчас {$date}",
					'user_id' => $data->object->user_id,
					'access_token' => $communityToken,
					'v' => $version
					);
			break;
			
			
			default:
				//затем с помощью users.get получаем данные об авторе
				$userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$data->object->user_id}&v={$version}"));

				//и извлекаем из ответа его имя
				$user_name = $userInfo->response[0]->first_name;

				//С помощью messages.send и токена сообщества отправляем ответное сообщение
				$request_params = array(
					'message' => "Привет, {$user_name}!<br>".
									"Если хочешь узнать, что я могу, напиши \"help me\" или \"нужна помощь\"",
					'user_id' => $data->object->user_id,
					'access_token' => $communityToken,
					'v' => $version
				);
		}

			$get_params = http_build_query($request_params);

			file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);

		//Возвращаем "ok" серверу Callback API
		echo('ok');

    break;
}
?>