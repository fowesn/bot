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
        if($type == "random")
            $params = array("user" => $userID, "service" => "vk");
        else
            $params = array("filter" => $type, "user" => $userID, "service" => "vk");

        $request_params = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, HOST_API . '/assignments');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request_params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch));

//////////////////////////////////

        //проверка кодов http
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($code == 201) {

            // если ошибок нет, то собирается сбщ с заданием
            $uniqueNumber = $result->data->problem;
            $message = "Задание номер " . $uniqueNumber . ".\r\n\r\n";
            // куча напоминаний о том, как прислать ответ и попросить разбор
            $message .= "Чтобы отправить мне ответ на это задание, напиши \"" . $uniqueNumber . " [ответ]\".\r\n" .
                "Если ты ещё не умеешь решать такие задания, я могу объяснить его тебе. Для этого напиши мне \"разбор " . $uniqueNumber . "\".\r\n" .
                "Если ты хочешь узнать правильный ответ, напиши \"ответ " . $uniqueNumber . "\".\r\n";
            for ($i = 0; $i < count($result->data); $i++)
                switch ($result->data->resources[$i]->type) {
                    case 'pdf':
                        // тут нужен attachment документа
                        $attachment = VKAPI::documentAttachmentMessageSend($userID, $result->data->resources[$i]->content,
                            "задание " . $uniqueNumber, "бот по информатике");
                        break;
                    case 'изображение':
                        // attachment изображения
                        $attachment = VKAPI::pictureAttachmentMessageSend($userID, $result->data->resources[$i]->content);
                        break;
                    case 'ссылка':
                        $message .= "\r\n" . $result->data->resources[$i]->content;
                        break;
                    case 'текст':
						if(preg_match("#^http#i", $result->data->resources[$i]->content))
							$attachment = VKAPI::pictureAttachmentMessageSend($userID, $result->data->resources[$i]->content);
						else
							$message .= "\r\n" . $result->data->resources[$i]->content;
                        break;
                    default:
                        $message .= "\r\n" . $result->data->resources[$i]->content;
                        break;
                }
        }
        else if ($code == 200)
        {
            switch($result->status)
            {
                case "a0-2":
                    $message = "Прости, но у меня больше нет заданий :(";
                    break;
                case "a0-3":
                    $message = "Беда! У меня закончились задания по этой теме :(";
                    break;
                case "a0-4":
                    $message = "Прости, у меня больше нет заданий по этому номеру :(";
                    break;
                default:
                    $message = "Кажется, у меня больше нет заданий. Попробуй написать мне позже!";
            }
        }
        else if ($code == 422)
        {
            switch($result->status)
            {
                case "a0-0":
                    $message = "Заданий на такую тему у меня нет. Чтобы узнать, на какие темы у меня есть задания, напиши \"темы\"";
                    break;
                case "a0-1":
                    $message = "Такого номера нет в КИМе!";
                    break;
                default:
                    $message = "У меня не получается прислать тебе задание, попробуй ещё раз.";
            }
        }
        else
            $message = $code . ". " . self::$server_error_message . ' ' . $type;

        if(isset($attachment))
            return array("user_id" => $userID, "message" => $message, "attachment" => $attachment);
        else
            return array("user_id" => $userID, "message" => $message);
    }
}
