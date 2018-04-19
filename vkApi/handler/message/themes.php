<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 19.04.18
 * Time: 18:49
 */

namespace vkApi\handler\message;

use \vkApi\Api;
use \serverApi\UnidentifiedPartialRequests;
use \serverApi\OtherRequests;
class themes {
	public static function run($user_message,$data)
	{
		if(count($user_message) > 1)
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => UnidentifiedPartialRequests::themes()));
		else
			Api::messageSend(array("user_id" => $data->object->user_id,
				"message" => OtherRequests::getThemesList()));
	}
}