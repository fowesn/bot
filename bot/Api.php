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
			throw new RequestError(__FILE__." : ".__LINE__." ".$result->error->error_msg, $result->error->error_code);

	}

    /**
	 *
	 * @param $user_id string получатель изображения
	 * @param $image_path string путь до файла изображения
     * @throws \Exception
	 * @return array массив полей загруженного изображения на сервер вк
     */

    static public function pictureAttachmentMessageSend($user_id, $image_path) {
		/** @var  $request_params - array параметров к запросу .... */
        $request_params = array("peer_id" => $user_id);
        $request_params = self::setVersionAndToken($request_params);
		$result = json_decode(file_get_contents('https://api.vk.com/method/photos.getMessagesUploadServer?' . http_build_query($request_params)));

        /** В случае ошибки запроса */
        if(isset($result->error))
            throw new RequestError(__FILE__." : ".__LINE__." ".$result->error->error_msg . " " . 'https://api.vk.com/method/photos.getMessagesUploadServer?' .
                http_build_query($request_params), $result->error->error_code);

		/** @var  $server - сервер загрузки изображения */
		$server = parse_url($result->response->upload_url);
		/** @var $result - содержит ответ сервера на загрузку изображения */
		$result = LoadFile::sendData($server, LoadFile::getImage($image_path));

		/** temp */
		$result = preg_split('/\n/',$result);

		$photo_server_json = json_decode($result[count($result)-1]);
        /** В случае неудачной отправки */


        if(isset($photo_server_json->error))
            throw new RequestError(__FILE__." : ".__LINE__.$photo_server_json->error,$photo_server_json->error->error_code);

		/** @var  $photo_save array параметры для сохранения фото на сервере */
        $photo_save = array(
            "server" => $photo_server_json->server,
            "hash" => $photo_server_json->hash,
			"photo" =>$photo_server_json->photo
            );
        $photo_save = self::setVersionAndToken($photo_save);

		/** @var array $result Получение id и других параметров изображения на сервере VK */
        $result = json_decode(file_get_contents('https://api.vk.com/method/photos.saveMessagesPhoto?' . http_build_query($photo_save)));
        /** В случае если произошла ошибка */
        if (isset($result->error))
           throw new RequestError(__FILE__." : ".__LINE__.$result->error->error_msg,$result->error->error_code);

		/** @var array $result */
		if (isset($result->response[0])) {
			$result = $result->response[0];
		}

        return $result;
    }

	/**
	 * @param $user_id string -  id пользователя
	 * @param $document_path string - url к документу
	 * @return array[]|false|mixed|string|string[] - возвращает информацию о документе
	 * @throws RequestError в случае ошибок при запросе к вк API
	 * @throws \Exception - в случае отсутсвие файла
	 */
	static public function documentAttachmentMessageSend($user_id, $document_path) {

		/** @var  $request_params - array параметров к запросу .... */
		$request_params = array("peer_id" => $user_id, "type" => "doc");
		$request_params = self::setVersionAndToken($request_params);
		$result = json_decode(file_get_contents('https://api.vk.com/method/docs.getMessagesUploadServer?' . http_build_query($request_params)));
		/** проверка на успешность */
		if (isset($result->error))
			throw new RequestError(__FILE__ . " : " . __LINE__ . " " . $result->error->error_msg . " " . 'https://api.vk.com/method/docs.getMessagesUploadServer?' .
				http_build_query($request_params), $result->error->error_code);
		/** @var  $server - сервер загрузки изображения */
		$server = parse_url($result->response->upload_url);

		/** @var $result - содержит ответ сервера на загрузку изображения */
		$result = LoadFile::sendData($server, LoadFile::getDocument($document_path));

		/** temp */
		$result = preg_split('/\n/', $result);
		$document_server_json = json_decode($result[count($result) - 1]);
		if (isset($document_server_json->error))
			throw new RequestError(__FILE__ . " : " . __LINE__ . $document_server_json->error, $document_server_json->error->error_code);
		/** @var array $document_save - параметры для сохранения документа */
		$document_save = array(
			"file" => $document_server_json->file,
			"title" => "Задание",
			"tags" => "персональный бот"
		);
		$document_save = self::setVersionAndToken($document_save);
		$result = json_decode(file_get_contents('https://api.vk.com/method/docs.save?' . http_build_query($document_save)));
		if (isset($result->error))
			throw new RequestError(__FILE__ . " : " . __LINE__ . $result->error->error_msg, $result->error->error_code);

		if (isset($result->response[0])) {
			$result = $result->response[0];
		}
		return $result;


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
	public function __construct($message, $code = 0, \Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

