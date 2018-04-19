<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 19.04.18
 * Time: 18:44
 */

namespace vkApi\handler\message;
use \vkApi\Api;
use \serverApi\UnidentifiedPartialRequests;
class anasysis {
	public static function run($user_message,$data)
	{
		if(count($user_message) != 2)
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => UnidentifiedPartialRequests::anasysis()));
		else
			Api::messageSend(\serverApi\Answer::getAnalysis($data->object->user_id, $user_message[1]));

	}
}