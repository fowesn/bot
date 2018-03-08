<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 26.02.18
 * Time: 15:53
 */
include_once "iHandler.php";
include_once "message/help.php";

class message_new implements iHandler {
	public static function run($data) {
		\api\Api::messageSend(\help::run($data));
	}
}
