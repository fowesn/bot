<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 19.04.18
 * Time: 18:47
 */

namespace vkApi\handler\message;
use \vkApi\Api;
use \serverApi\UnidentifiedPartialRequests;
class task {
	public static function run($user_message,$data)
	{
		if(count($user_message) > 2)
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => UnidentifiedPartialRequests::tasks()));
		else if(count($user_message) == 2) {
			if (preg_match("/^\d+$/", $user_message[1]))
				Api::messageSend(\serverApi\Task::getKIMTaskMessage($data->object->user_id, $user_message[1]));
			else
				Api::messageSend(\serverApi\Task::getThemeTaskMessage($data->object->user_id, $user_message[1]));
		}
	}
}