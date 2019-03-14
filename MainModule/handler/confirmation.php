<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 08.03.18
 * Time: 15:52
 */
namespace MainModule\handler;

class confirmation {

	public static function run($data) {
		echo CONFIRMATION_TOKEN;
		exit();
	}
}