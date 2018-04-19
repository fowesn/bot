<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 21.02.18
 * Time: 23:29
 * @version 0.1
 * @author kurenchuksergey
 * @package api
 */

namespace api;
class Api {

	/**
	 * @param $request_params array параметров
	 *        user_id - id получателя
	 *        message - сообщение
	 *        attachment - вложение (файл должен быть отдельно загружен на сервер)
	 *
	 * @throws \Exception  в случае отсутсвие user_id
	 * @throws RequestError в случае ошибки отправки сообщение (на стороне вк),
	 * message содержит описание от вк
	 * code содержит код ответа вк
	 */
	static public function messageSend($request_params) {
	    if (!isset($request_params['user_id'])) {
			throw new \Exception("Не указан user_id");
		}
        if (!isset($request_params['message'])) {
            throw new \Exception("Не указан message");
        }
		//в случае если api version и access_token не установлены
		$request_params = self::setVersionAndToken($request_params);

		//отправляем
		$get_params = http_build_query($request_params);
		$result = json_decode(file_get_contents('https://api.vk.com/method/messages.send?' . $get_params));
		//обрабатываем ошибки
        if (!isset($result->error))
			return;
		else
			throw new RequestError($result->error->error_msg, $result->error->error_code);

	}

    /**
     * @param
     * @throws \Exception
     */
    static public function pictureAttachmentMessageSend($user_id) {

        $group_id = "123539368";
        $album_id = "253228018";
        $image_path = dirname(__FILE__) . '/img.jpg';

        self::messageSend(array("user_id" => $user_id, "message" => "пока что всё ок"));

        $group_request_params = array("group_id" => $group_id, "album_id" => $album_id);
        $group_request_params = self::setVersionAndToken($group_request_params);
        $result = json_decode(file_get_contents('https://api.vk.com/method/photos.getUploadServer?' . http_build_query($group_request_params)));
        if(isset($result->error))
            //throw new RequestError($result->error->error_msg, $result->error->error_code);
            self::messageSend(array("user_id" => $user_id, "message" => "err" . $result->error->error_msg . "  " . $result->error->error_code));

        self::messageSend(array("user_id" => $user_id, "message" => "до сих пор ок"));
        $server = $result->response->upload_url;
        $postparam = array("file1" => "@" . $image_path);
        //Отправляем файл на сервер
        $ch = curl_init($server);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postparam);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data; charset=UTF-8'));
        $photo_server_json = json_decode(curl_exec($ch));
        curl_close($ch);
        if(isset($photo_server_json->error))
            self::messageSend(array("user_id" => $user_id, "message" => "err"));
        self::messageSend(array("user_id" => $user_id, "message" => "почему тогда его нет в альбоме?"));
        $photo_save = array(
            "server" => $photo_server_json->server,
            "photos_list" => $photo_server_json->photos_list,
            "album_id" => $album_id,
            "hash" => $photo_server_json->hash,
            'gid' => $group_id);
        $photo_save = self::setVersionAndToken($photo_save);
        $result = json_decode(file_get_contents('https://api.vk.com/method/photos.save?' . http_build_query($photo_save)));
        if (!isset($result->error))
            return;
        else {
            self::messageSend(array("user_id" => $user_id, "message" => "err"));
        }
        self::messageSend(array("user_id" => $user_id, "message" => "тут явно чето не так"));
        //throw new RequestError($result->error->error_msg, $result->error->error_code);
        return;
    }
	/**
	 *  Метод является оберткой метода getHistory, в коем не участвует
	 * параметр start_message_id
	 * @param $request_params array параметров
	 *         user_id - id получателя
	 *         offset - смещение
	 *         count - кол-во
	 * @return mixed - json сообщений
	 * @throws RequestError - в случае ошибки в запросе(со стороны вк), содержит сообщение и код от вк
	 * @throws \Exception - в случае отсутствие обязательных полей
	 */
	static public function getLastMessages($request_params) {
		if (!isset($request_params['user_id'])) {
			throw new \Exception("Не указан user_id");
		}
		if (!isset($request_params['offset']) or $request_params['offset'] < 0) {
			throw new \Exception("Не указано смещение");
		}
		if (!isset($request_params['count']) or $request_params['offset'] < 0) {
			throw new \Exception("Не указано кол-во сообщений");
		}

		if (isset($request_params['start_message_id']))
			$request_params['start_message_id'] = -1;
		//в случае если api version и access_token не установлены
		$request_params = self::setVersionAndToken($request_params);

		//отправляем
		$get_params = http_build_query($request_params);
		$result = json_decode(file_get_contents('https://api.vk.com/method/messages.getHistory?' . $get_params));
		//обрабатываем ошибки
		if (!isset($result->error))
			return $result;
		else
			throw new RequestError($result->error->error_msg, $result->error->error_code);
	}

	/**
	 * @param $request_params array
	 *          user_id - id пользователя
	 *          fields - необходимые данные, подробнее тут (https://vk.com/dev/objects/user) , параметры через запятую
	 *          name_case - склонение имени пользователя
	 *          Возможные значения: именительный – nom, родительный – gen, дательный – dat,
	 *          винительный – acc, творительный – ins, предложный – abl. По умолчанию nom.
	 * @return mixed json user info
	 * @throws RequestError
	 * @throws \Exception
	 */
	static public function getUserInfo($request_params) {
		if (!isset($request_params['user_id'])) {
			throw new \Exception("Не указан user_id");
		}
		if (!isset($request_params['fields']) or empty($request_params['fields'])) {
			throw new \Exception("Не указаны параметры fields");
		}
		$request_params = self::setVersionAndToken($request_params);
		//отправляем
		$get_params = http_build_query($request_params);
		$result = json_decode(file_get_contents('https://api.vk.com/method/users.get?' . $get_params));
		if (!isset($result->error))
			return $result;
		else
			throw new RequestError($result->error->error_msg, $result->error->error_code);


	}

	/**
	 * Функция для внутреннего использования, устанавливает в запросе версию и токен
	 * @param $request_params array запроса
	 * @return array
	 */
	static private function setVersionAndToken($request_params) {
		if (!isset($request_params['v'])) {
			$request_params['v'] = VERSION_VK_API;
		}
		if (!isset($request_params['access_token'])) {
			$request_params['access_token'] = COMMUNITY_TOKEN;
		}
		return $request_params;
	}
}


/*
 * Класс исключения для работы с вк api
 * обработка кодов возврата в случае неудачи
 */

class RequestError extends \Exception {
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}