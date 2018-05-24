<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */

namespace api\handler\message;
class Answer
{
	private static $server_error_message = "Что-то пошло не так. Попробуй снова!";
	private static $url = 'http://kappa.cs.petrsu.ru/~nestulov/API/public/index.php/problems/';

    /**
     * @param $userId
     * @param $taskId
     * @return array
     * @throws \Exception
     */
	public static function getAnswer($userId, $taskId) {
	    if(!isset($userId))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
		$task = (int)$userId ^ (int)$taskId;
		$params = array("problem_id" => $task, "user_id" => $userId, "service" => "vk");
		$request_params = http_build_query($params);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$url . 'answer?' . $request_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		$result = json_decode($result);
		//проверка кодов http
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($code != 200) {
			$message = $code . ". " . self::$server_error_message;
			return array("user_id" => $userId, "message" => $message);
		}

		//ошибки пользователя
		if ($result->success !== "true") {
			$message = $result->error->message;
		} else {
			// если ошибок нет, то собирается сообщение с ответом
			$message = $result->answer;
		}
		return array("user_id" => $userId, "message" => $message);
	}

    /**
     * @param $userId
     * @param $taskId
     * @return array
     * @throws \Exception
     * @throws \api\RequestError
     */
	public static function getAnalysis($userId, $taskId) {
        if(!isset($userId))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
		$task = (int)$userId ^ (int)$taskId;
		$params = array("problem_id" => $task, "user_id" => $userId, "service" => "vk");
		$request_params = http_build_query($params);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$url . 'solution?' . $request_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		$result = json_decode($result);
		//проверка кодов http
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($code != 200) {
			$message = $code . ". " . self::$server_error_message;
			return array("user_id" => $userId, "message" => $message);
		}

		//ошибки пользователя
		if ($result->success !== "true") {
			$message = $result->error->message;
		} else {
			// если ошибок нет, то собирается сбщ с разбором
			$message = "";
			for ($i = 0; $i < count($result->data); $i++)
				switch ($result->data[$i]->type) {
					case 'pdf-файл':
						// тут нужен attachment документа
						$attachment = \api\Api::documentAttachmentMessageSend($userId,$result->data[$i]->content,
							"разбор " . $taskId, "бот по информатике");
						break;
					case 'изображение':
						// attachment изображения
						$attachment = \api\Api::pictureAttachmentMessageSend($userId,$result->data[$i]->content);
						break;
					case 'ссылка':
						$message = $result->data[$i]->content;
						break;
					case 'текст':
						if(preg_match("#^http#i", $result->data[$i]->content))
							$attachment = \api\Api::pictureAttachmentMessageSend($userId, $result->data[$i]->content);
						else
							$message .= "\r\n" . $result->data[$i]->content;
						break;
					default:
						break;
				}
		}
		if(isset($attachment))
            return array("user_id" => $userId, "message" => $message, "attachment" => $attachment);
		else
            return array("user_id" => $userId, "message" => $message);
	}

    /**
     * @param $userId
     * @param $taskId
     * @param $answer
     * @return array
     * @throws \Exception
     */
	public static function checkUserAnswer($userId, $taskId, $answer) {
        if(!isset($userId))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
		// post
		$task = (int)$userId ^ (int)$taskId;
		$params = array("problem_id" => $task, "answer" => $answer, "user_id" => $userId, "service" => "vk");
		$request_params = http_build_query($params);
		//$message = self::$url . "answer?" . $request_params;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$url . 'answer');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($ch));
		curl_close($ch);

		//проверка кодов http
//        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//        if ($code !== 200) {
//            $message = $code . ". " . self::$server_error_message;
//            return array("user_id" => $userId, "message" => $message);
//        }

		//ошибки пользователя
		if ($result->success !== "true") {
			$message = $result->error->message;
		} else {
			// если ошибок нет, то собирается сообщение с результатом проверки ответа пользователя
			$message = (bool)($result->result) ? "Верно" : "Неверно";
		}
        return array("user_id" => $userId, "message" => $message);

	}
}