<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */

namespace MainModule\handler\message;
class Answer
{
	private static $server_error_message = "Что-то пошло не так. Попробуй снова!";
	//private static $url = 'http://kappa.cs.petrsu.ru/~nestulov/API/v1/public/index.php/problems/';

    /**
     * @param $userID
     * @param $taskID
     * @return array
     * @throws \Exception
     */
	public static function getAnswer($userID, $taskID) {
	    if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
        //проверка кодов http
        $url = HOST_API . '/problems/' . $taskID . '/answer?' . http_build_query(array("user" => $userID, "service" => 'vk'));

        $code = substr(get_headers($url)[0], 9, 3);

        if($code == 200)
        {
            $result = json_decode(file_get_contents($url));
            if($result->status === 'a1-0')
                $message = "Задание с таким номером я тебе не выдавал. Чтобы посмотреть список номеров нерешённых тобой заданий, напиши мне \"задания\"";
            else
                $message = 'Ответ на задание ' . $taskID . ': ' . $result->data->answer;
        }
        else
            $message = $code . ". " . self::$server_error_message . "\r\n\r\n";

		return array("user_id" => $userID, "message" => $message);
	}

    /**
     * @param $userID
     * @param $taskID
     * @return array
     * @throws \Exception
     */
	public static function getAnalysis($userID, $taskID) {
        if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
		$task = (int)$userID ^ (int)$taskID;
		$params = array("problem_id" => $task, "user_id" => $userID, "service" => "vk");
		$request_params = http_build_query($params);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, HOST_API . '/problems/solution?' . $request_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		$result = json_decode($result);
		//проверка кодов http
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($code != 200) {
			$message = $code . ". " . self::$server_error_message;
			return array("user_id" => $userID, "message" => $message);
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
						$attachment = \MainModule\VKAPI::documentAttachmentMessageSend($userID,$result->data[$i]->content,
							"разбор " . $taskID, "бот по информатике");
						break;
					case 'изображение':
						// attachment изображения
						$attachment = \MainModule\VKAPI::pictureAttachmentMessageSend($userID,$result->data[$i]->content);
						break;
					case 'ссылка':
						$message = $result->data[$i]->content;
						break;
					case 'текст':
						if(preg_match("#^http#i", $result->data[$i]->content))
							$attachment = \MainModule\VKAPI::pictureAttachmentMessageSend($userID, $result->data[$i]->content);
						else
							$message .= "\r\n" . $result->data[$i]->content;
						break;
					default:
						break;
				}
		}
		if(isset($attachment))
            return array("user_id" => $userID, "message" => $message, "attachment" => $attachment);
		else
            return array("user_id" => $userID, "message" => $message);
	}

    /**
     * @param $userID
     * @param $taskID
     * @param $answer
     * @return array
     * @throws \Exception
     */
	public static function checkUserAnswer($userID, $taskID, $answer) {
        if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
		// post
		$task = (int)$userID ^ (int)$taskID;
		$params = array("problem_id" => $task, "answer" => $answer, "user_id" => $userID, "service" => "vk");
		$request_params = http_build_query($params);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, HOST_API . '/problems/answer');
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
//            return array("user_id" => $userID, "message" => $message);
//        }

		//ошибки пользователя
		if ($result->success !== "true") {
			$message = $result->error->message;
		} else {
			// если ошибок нет, то собирается сообщение с результатом проверки ответа пользователя
			$message = (bool)($result->result) ? "Верно" : "Неверно";
		}
        return array("user_id" => $userID, "message" => $message);

	}
}