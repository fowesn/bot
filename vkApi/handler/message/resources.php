<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 19.04.18
 * Time: 18:41
 */

namespace vkApi\handler\message;
use \vkApi\Api;
use \serverApi\UnidentifiedPartialRequests;
use \serverApi\OtherRequests;

class resources {
	public static function run($user_message,$data)
	{
		if(count($user_message) > 1)
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => UnidentifiedPartialRequests::resources()));
		else
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => OtherRequests::getResourceTypesList()));
	}
}