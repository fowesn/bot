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
    //private static $url = 'http://kappa.cs.petrsu.ru/~nestulov/API/v1/public/index.php/problems/problem?';

    /**
     * @param $userID
     * @return array
     * @throws \Exception
     */
    public static function getRandomTaskMessage($userID)
    {
        if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
        return self::getTask("random", $userID);
    }

    /**
     * @param $userID
     * @param $theme
     * @return array
     * @throws \Exception
     */
    public static function getThemeTaskMessage($userID, $theme) {
        if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
        if(!isset($theme))
        throw  new \Exception(__FILE__ . " : " . __LINE__ . " Не указан theme");
        return self::getTask($theme, $userID);
    }

    /**
     * @param $userID
     * @param $KIMid
     * @return array
     * @throws \Exception
     */
    public static function getKIMTaskMessage($userID, $KIMid) {
        if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
        if(!isset($KIMid))
        throw  new \Exception(__FILE__ . " : " . __LINE__ . " Не указан KIMid");

        return self::getTask($KIMid, $userID);
    }

    /**
     * @param $userID
     * @return array
     */
    public static function getIncompletedTasksList($userID)
    {
        $message = $userID;
        return array("user_id" => $userID, "message" => $message);
    }

    public static function getTaskAgain($userID, $taskID)
    {
        $message = $userID . ' ' . $taskID;
        return array("user_id" => $userID, "message" => $message);
    }

	/**
	 * @param $type - тип запроса задания к апи
	 * @param $userID - ид пользователя
	 * @return array - параметры запроса к вк апи
	 * @throws \Exception
	 */
    private static function getTask($type, $userID)
    {


        $params = array("type" => $type, "user_id" => $userID, "service" => "vk");
        $request_params = http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, HOST_API . '/problems/problem' . $request_params);
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
        }
        else {

            // если ошибок нет, то собирается сбщ с заданием
            $uniqueNumber = ((int)$userID) ^ (int)($result->problem);
            $message = "Задание номер " . $uniqueNumber . ".\r\n\r\n";
            // куча напоминаний о том, как прислать ответ и попросить разбор
            $message .= "Чтобы отправить мне ответ на это задание, напиши \"" . $uniqueNumber . " [ответ]\".\r\n" .
                "Если ты ещё не умеешь решать такие задания, я могу объяснить его тебе. Для этого напиши мне \"разбор " . $uniqueNumber . "\".\r\n" .
                "Если ты хочешь узнать правильный ответ, напиши \"ответ " . $uniqueNumber . "\".\r\n";
            for ($i = 0; $i < count($result->data); $i++)
                switch ($result->data[$i]->type) {
                    case 'pdf-файл':
                        // тут нужен attachment документа
                        $attachment = VKAPI::documentAttachmentMessageSend($userID, $result->data[$i]->content,
                            "задание " . $uniqueNumber, "бот по информатике");
                        break;
                    case 'изображение':
                        // attachment изображения
                        $attachment = VKAPI::pictureAttachmentMessageSend($userID, $result->data[$i]->content);
                        break;
                    case 'ссылка':
                        $message .= "\r\n" . $result->data[$i]->content;
                        break;
                    case 'текст':
						if(preg_match("#^http#i", $result->data[$i]->content))
							$attachment = VKAPI::pictureAttachmentMessageSend($userID, $result->data[$i]->content);
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
}
