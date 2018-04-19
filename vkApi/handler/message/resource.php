<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 19.04.18
 * Time: 18:38
 */

namespace vkApi\handler\message;
use \vkApi\Api;
use \serverApi\UnidentifiedPartialRequests;
use \serverApi\OtherRequests;
class resource {
	public static function run($user_message,$data)
	{
		if(count($user_message) != 2)
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => UnidentifiedPartialRequests::resource()));
		else
			Api::messageSend(OtherRequests::setUserPreferredResource($data->object->user_id, $user_message[1]));
	}
}