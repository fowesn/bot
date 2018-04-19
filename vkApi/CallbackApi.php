<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 26.02.18
 * Time: 14:13
 */

namespace vkApi;


//include_once "handler/message_new.php";
include_once "handler/confirmation.php";

/**
 * Class CallbackApi Реализует распределение запросов событий по модулям обработки.
 * @package vkApi
 * @author kurenchuksergey
 * @version 0.1
 */
class CallbackApi {
	/**
	 * Функция входа
	 * @return array параметров
	 *        user_id - id получателя
	 *        message - сообщение
	 *        attachment - вложение (файл должен быть отдельно загружен на сервер)
	 *
	 * @param $data array полученный json от вк
	 * @throws EventNotSupported в случае когда нет обработчика собиытия
	 * @throws SecurityBreach в случае аттаки
	 * @throws \Exception когда данные не корректны
	 */
	public static function requestHandler($data) {
		//echo "test";
		//echo var_dump($data);
		if (!isset($data->type))
			throw new \Exception("Нет типа события");
		if (!isset($data->secret) or $data->secret != SECRET_KEY)
			throw new SecurityBreach("Ключ не соответствует");


		if (class_exists('\\vkApi\\handler\\'.$data->type, true)) {
			return call_user_func('\\vkApi\\handler\\'.$data->type . "::run", $data);
		} else
			throw new EventNotSupported("Метода " . $data->type . " нет в " . __NAMESPACE__ . "\\handler\\" . $data->type);


	}


}

/**
 * Class SecurityBreach возникает в случае неверного ключа
 * @package vkApi
 */
class SecurityBreach extends \Exception {
	public function __construct($message, $code = 0, \Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

/**
 * Class EventNotSupported возникает в случае отсутсвие обработчика события
 * @package vkApi
 */
class EventNotSupported extends \Exception {
	public function __construct($message, $code = 0, \Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}
