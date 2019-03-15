<?php
/**
 * Created by PhpStorm.
 * User: fow
 * Date: 26.02.18
 * Time: 14:13
 */

namespace MainModule;

/**
 * Class RequestHandler Реализует распределение запросов событий по модулям обработки.
 * @package MainModule
 * @author fow
 * @version 0.2
 */
class RequestHandler {
	/**
	 * Функция входа
	 * @return array параметров
	 *        user_id - id получателя
	 *        message - сообщение
	 *        attachment - вложение (файл должен быть отдельно загружен на сервер)
	 *
	 * @param $data array полученный json от вк
	 * @throws \Exception когда данные не корректны/нет обработчика события или в случае атаки
	 */
	public static function requestHandler($data) {
		if (!isset($data->type)) {

			throw new \Exception(__FILE__ . " : " . __LINE__ . " Нет типа события ");

		}

		if (!isset($data->secret) or $data->secret != SECRET_KEY)
			throw new \Exception(__FILE__ . " : " . __LINE__ . "Ключ не соответствует");


		switch ($data->type)
        {
            case 'message_new':
                \MainModule\handler\message_new::parse($data);
                break;
            case 'confirmation':
                \MainModule\handler\confirmation::confirm();
                break;
            default:
                throw new \Exception(__FILE__ . " : " . __LINE__ . "Обработчика события " . $data->type . " нет в MainModule\\handler\\" . $data->type);
        }
	}


}