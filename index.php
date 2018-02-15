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
//////////////AutoLoader//////////////////////////

spl_autoload_register
(
	function ($className) {
		$siteRoot = dirname(__FILE__);

		$classFullName = ltrim
		(
			implode
			(
				DIRECTORY_SEPARATOR,
				explode
				(
					'\\',
					$className
				)
			),
			DIRECTORY_SEPARATOR
		);

		$fileFullName = $siteRoot . DIRECTORY_SEPARATOR . $classFullName . '.php';

		if (!file_exists($fileFullName)) {
			throw new \Exception ('File ' . $fileFullName . ' not found!');
		}

		require_once($fileFullName);

		if (!class_exists($className)) {
			throw new \Exception ('Class ' . $className . ' not found!');
		}
	}
);



/////////////////////////////////////////////////
//$data = json_decode(file_get_contents('php://input'));
//не норма
/*$data = json_decode($_GET['data']);

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
                $garbage = new garbage\HelpRequest($data);
				
			break;
			
			case 'время':
                $garbage = new garbage\TimeRequest($data);
			break;
			
			
			default:
                //затем с помощью users.get получаем данные об авторе
               // $garbage = new garbage\ErrorRequest($data);
                $garbage = new garbage\Test($data);
        }


        $get_params = http_build_query($garbage->getResult());

			file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);

		//Возвращаем "ok" серверу Callback API

		echo('ok');

    break;
}
*/
try {
	echo "<pre>";
	echo var_dump(\api\Api::getUserInfo(array("user_id" => 0, 'fields' => 'photo_50,city,verified'))) . "</pre>";
} catch (\api\RequestError $err) {
	echo $err->getMessage() . "<br>";
	echo $err->getCode();
} catch (\Exception $err) {
	echo $err->getMessage();
}
?>

