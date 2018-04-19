<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 19.04.18
 * Time: 18:36
 */

namespace vkApi\handler\message;
use \vkApi\Api;
use \serverApi\UnidentifiedPartialRequests;


class answer {
	public static function run($user_message,$data)
	{
		if(count($user_message) != 2)
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => UnidentifiedPartialRequests::answer()));
		else
			Api::messageSend(\serverApi\Answer::getAnswer($data->object->user_id, $user_message[1]));
	}
}