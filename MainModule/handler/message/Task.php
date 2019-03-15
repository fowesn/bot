<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */

namespace MainModule\handler\message;

use MainModule\VKAPI;

class Task
{
    private static $server_error_message = "Что-то пошло не так. Попробуй снова!";
    private static $url = 'http://kappa.cs.petrsu.ru/~nestulov/API/public/index.php/problems/problem?';

    /**
     * @param $userId
     * @return array
     * @throws \Exception
     */
    public static function getRandomTaskMessage($userId)
    {
        if(!isset($userId))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
        return self::getTask("random", $userId);
    }

    /**
     * @param $userId
     * @param $theme
     * @return array
     * @throws \Exception
     */
    public static function getThemeTaskMessage($userId, $theme) {
        if(!isset($userId))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
        if(!isset($theme))
        throw  new \Exception(__FILE__ . " : " . __LINE__ . " Не указан theme");
        return self::getTask($theme, $userId);
    }

    /**
     * @param $userId
     * @param $KIMid
     * @return array
     * @throws \Exception
     */
    public static function getKIMTaskMessage($userId, $KIMid) {
        if(!isset($userId))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
        if(!isset($KIMid))
        throw  new \Exception(__FILE__ . " : " . __LINE__ . " Не указан KIMid");

        return self::getTask($KIMid, $userId);
    }

	/**
	 * @param $type - тип запроса задания к апи
	 * @param $userId - ид пользователя
	 * @return array - параметры запроса к вк апи
	 * @throws \Exception
	 */
    private static function getTask($type, $userId)
    {


        $params = array("type" => $type, "user_id" => $userId, "service" => "vk");
        $request_params = http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$url . $request_params);
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
        }
        else {

            // если ошибок нет, то собирается сбщ с заданием
            $uniqueNumber = ((int)$userId) ^ (int)($result->problem);
            $message = "Задание номер " . $uniqueNumber . ".\r\n\r\n";
            // куча напоминаний о том, как прислать ответ и попросить разбор
            $message .= "Чтобы отправить мне ответ на это задание, напиши \"" . $uniqueNumber . " [ответ]\".\r\n" .
                "Если ты ещё не умеешь решать такие задания, я могу объяснить его тебе. Для этого напиши мне \"разбор " . $uniqueNumber . "\".\r\n" .
                "Если ты хочешь узнать правильный ответ, напиши \"ответ " . $uniqueNumber . "\".\r\n";
            for ($i = 0; $i < count($result->data); $i++)
                switch ($result->data[$i]->type) {
                    case 'pdf-файл':
                        // тут нужен attachment документа
                        $attachment = VKAPI::documentAttachmentMessageSend($userId, $result->data[$i]->content,
                            "задание " . $uniqueNumber, "бот по информатике");
                        break;
                    case 'изображение':
                        // attachment изображения
                        $attachment = VKAPI::pictureAttachmentMessageSend($userId, $result->data[$i]->content);
                        break;
                    case 'ссылка':
                        $message .= "\r\n" . $result->data[$i]->content;
                        break;
                    case 'текст':
						if(preg_match("#^http#i", $result->data[$i]->content))
							$attachment = VKAPI::pictureAttachmentMessageSend($userId, $result->data[$i]->content);
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
}
