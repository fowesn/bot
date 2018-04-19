<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 19.04.18
 * Time: 18:11
 */

namespace vkApi\handler\message;
use \vkApi\Api;

class help {
	public static function run($user_message,$data){
		if(count($user_message) > 1)
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => \serverApi\UnidentifiedPartialRequests::help()));
		else {
			$message = \serverApi\OtherRequests::GetHelpMessage();
			for($i = 0; $i < count($message); $i++)
				Api::messageSend(array("user_id" => $data->object->user_id,
					"message" => $message[$i]));
		}

	}
}