<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 19.04.18
 * Time: 18:28
 */
namespace vkApi\handler\message;
use \vkApi\Api;
class byDefault {
	public static function run($user_message,$data)
	{
		if (preg_match("/^\d+$/", $user_message[0]))
			//$user_message[1] - ответ
			//модуль проверки ответа, передать user_message[1]
			;
		else
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => \serverApi\OtherRequests::getBasicMessage()));
	}
}