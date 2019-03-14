<?php
/**
 * Created by PhpStorm.
 * User: fow
 * Date: 26.02.18
 * Time: 14:13
 */

namespace MainModule;

/**
 * Class CallbackApi Реализует распределение запросов событий по модулям обработки.
 * @package MainModule
 * @author fow
 * @version 0.2
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
		if (!isset($data->type)) {

			throw new \Exception(__FILE__ . " : " . __LINE__ . " Нет типа события ");

		}

		if (!isset($data->secret) or $data->secret != SECRET_KEY)
			throw new SecurityBreach("Ключ не соответствует");


		if (class_exists('\\MainModule\\handler\\' . $data->type, true)) {
			return call_user_func('\\MainModule\\handler\\' . $data->type . "::run", $data);
		} else
			throw new EventNotSupported("Обработчика события " . $data->type . " нет в MainModule\\handler\\" . $data->type);


	}


}

/**
 * Class EventNotSupported возникает в случае отсутсвие обработчика события
 * @package MainModule
 */
class EventNotSupported extends \Exception {
    public function __construct($message, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
/**
 * Class SecurityBreach возникает в случае неверного ключа
 * @package MainModule
 */

class SecurityBreach extends \Exception {
    public function __construct($message, $code = 0, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}