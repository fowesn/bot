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
	 * @param $user_id получатель изображения
     * @param $image_path путь до файла изображения
     * @throws \Exception
	 * @return массив полей загруженного изображения на сервер вк
     */

    static public function pictureAttachmentMessageSend($user_id, $image_path) {
//

        /** @var  $type формат изображения */
        $type = exif_imagetype($image_path);
        /** @var  $mimeType string mime тип изображения для запроса */
        $mimeType = image_type_to_mime_type($type);


		$file = curl_file_create($image_path, $mimeType, 'filename.jpg');
//		$file = LoadFile::getImage($image_path);

		/** @var  $request_params  Надо попробовать с create ....*/
        $request_params = array("peer_id" => $user_id);
        $request_params = self::setVersionAndToken($request_params);
        $result = json_decode(file_get_contents('https://api.vk.com/method/photos.getMessagesUploadServer?' .
            "peer_id=" . $request_params["peer_id"] . "&access_token=" .
            $request_params["access_token"] . "&v=" . $request_params["v"]));

        /** В случае ошибки запроса */
        if(isset($result->error))
            throw new RequestError(__FILE__." : ".__LINE__." ".$result->error->error_msg . " " . 'https://api.vk.com/method/photos.getMessagesUploadServer?' .
                http_build_query($request_params), $result->error->error_code);


        /** @var  $server сервер загрузки изображения */

        $server = $result->response->upload_url;

        $server = parse_url($server);

//        /////////////////////////////////////////////
		///////////////////////////////////////////////////
		///
		///
		///
		///
		///
		///
		// устанавливаем соединение с сервером
        $time_start = time();
		$fp = fsockopen("ssl://".$server["host"], 443, $errno, $errstr, 5);
		if (!$fp)
			throw new \Exception("проблемы с сокетом".$server["host"]);
		$boundary = "9e99e84655473cf6";
		$content = LoadFile::getImage($image_path);
		fwrite($fp, 'POST '.$server["path"].'?'.$server["query"].' HTTP/1.1'."\r\n");
		fwrite($fp, 'Host: '.$server["host"]." \r\n");
		fwrite($fp, 'Content-Type: multipart/form-data; boundary='.$boundary."\r\n");
		fwrite($fp, 'Content-Length: '.strlen($content)."\r\n\r\n");
		fwrite($fp, $content);
		$result = '';

			while ( !feof($fp) ) $result .= fgets($fp, 1024);
			// закрываем соединение

		fclose($fp);
        $time_end = time();
        throw new \Exception($time_end - $time_start);

///		/** @var  $postParam поле с бинарным изображениям для POST запроса */
//        $postParam = LoadFile::getImage($image_path);
////		$postParam = array("photo"=>$file);
//        //Отправляем файл на сервер
////		$curlLog = fopen("curl.log","w");
//        $ch = curl_init($server);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_POSTFIELDS,$postParam);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data; boundary=------------------------9e99e84655473cf6'));
////		curl_setopt($ch, CURLOPT_VERBOSE,true);
////		curl_setopt($ch, CURLOPT_STDERR,$curlLog);
////		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data-alternate'));
//        /** @var  $photo_server_json Ответ на загрузку изображения */
//
//        $photo_server_json = json_decode(curl_exec($ch));
//        curl_close($ch);

//		fclose($curlLog);

		$result = preg_split('/\n/',$result);

		$photo_server_json = json_decode($result[count($result)-1]);
        /** В случае неудачной отправки */


        if(isset($photo_server_json->error))
            throw new RequestError(__FILE__." : ".__LINE__.$photo_server_json->error,$photo_server_json->error->error_code);

        /** @var  $photo_save параметры для сохранения фото на сервере */
        $photo_save = array(
            "server" => $photo_server_json->server,
            "hash" => $photo_server_json->hash,
			"photo" =>$photo_server_json->photo
            );
        $photo_save = self::setVersionAndToken($photo_save);

        /** @var Получение id и других параметров изображения на сервере VK $result */
        $result = json_decode(file_get_contents('https://api.vk.com/method/photos.saveMessagesPhoto?' . http_build_query($photo_save)));
        /** В случае если произошла ошибка */
        if (isset($result->error))
           throw new RequestError(__FILE__." : ".__LINE__.$result->error->error_msg,$result->error->error_code);

        $result = $result->response[0];
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
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

