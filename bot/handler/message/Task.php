<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */
include_once "../../../setting.php";
class Task
{
    private static $server_error_message = "Что-то пошло не так. Попробуй снова!";
    private static $url = 'http://kappa.cs.petrsu.ru/~nestulov/API/public/index.php/problems/problem?';
    public static function getRandomTaskMessage($userId)
    {
        //формирование параметров запроса к апи
        $params = array("type" => "random", "user_id" => $userId, "service" => "vk");
        $request_params = http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$url . $request_params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        \api\Api::messageSend(array('user_id' => $userId, "message" => $result));
        //$result = file_get_contents(self::$url . $request_params);
        $result = json_decode($result);
        //проверка кодов http
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($code != 200) {
            $message = $code . ". " . self::$server_error_message . "\r\n\r\n";
            return array("user_id" => $userId, "message" => $message);
        }


        //ошибки пользователя
        if ($result->success !== "true") {
            $message = $result->error->message;
        }
        else {

            // если ошибок нет, то собирается сбщ с заданием
            $message = "Задание номер " . $userId ^ $result->problem . ".\r\n\r\n";

            // куча напоминаний о том, как прислать ответ и попросить разбор
            $message .= "Чтобы отправить мне ответ на это задание, напиши \"" . $userId ^ $result->problem . "\".\r\n" .
                "Если ты ещё не умеешь решать такие задания, я могу объяснить его тебе. Для этого напиши мне \"разбор" . $userId ^ $result->problem . "\".\r\n" .
                "Если ты хочешь узнать правильный ответ, напиши \"ответ" . $userId ^ $result->problem . "\".\r\n\r\n";
            for ($i = 0; $i < count($result->data); $i++)
                switch ($result->data[$i]->type) {
                    case 'pdf':
                        // тут нужен attachment документа
                        break;
                    case 'image':
                        // attachment изображения
                        break;
                    case 'link':
                        $message .= $result->data[$i]->resource_content . "\r\n\r\n";
                        break;
                    case 'text':
                        $message .= $result->data[$i]->resource_content;
                        break;
                    default:
                        break;
                }
        }
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getThemeTaskMessage($userId, $theme) {
        //формирование параметров запроса к апи
        $params = array("type" => $theme, "user_id" => $userId, "service" => "vk");
        $request_params = http_build_query($params);

        //проверка кодов http
        $code = substr(get_headers(self::$url . $request_params)[0], 9, 3);
        if ($code != 200) {
            $message = $code . " " . self::$server_error_message . "\r\n\r\n";
            return array("user_id" => $userId, "message" => $message);
        }
        $result = json_decode(file_get_contents(self::$url . $request_params));
        //ошибки пользователя
        if ($result->success !== "true") {
            $message = $result->error->message;
        }
        else {
            // если ошибок нет, то собирается сбщ с заданием
            $message = "Задание номер " . $userId ^ $result->problem . ".\r\n\r\n";

            // куча напоминаний о том, как прислать ответ и попросить разбор
            $message .= "Чтобы отправить мне ответ на это задание, напиши \"" . $userId ^ $result->problem . "\".\r\n" .
                "Если ты ещё не умеешь решать такие задания, я могу объяснить его тебе. Для этого напиши мне \"разбор" . $userId ^ $result->problem . "\".\r\n" .
                "Если ты хочешь узнать правильный ответ, напиши \"ответ" . $userId ^ $result->problem . "\".\r\n\r\n";
            for ($i = 0; $i < count($result->data); $i++)
                switch ($result->data[$i]->type) {
                    case 'pdf':
                        // тут нужен attachment документа
                        break;
                    case 'image':
                        // attachment изображения
                        break;
                    case 'link':
                        $message .= $result->data[$i]->resource_content . "\r\n\r\n";
                        break;
                    case 'text':
                        $message .= $result->data[$i]->resource_content;
                        break;
                    default:
                        break;
                }
        }
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getKIMTaskMessage($userId, $KIMid) {
        if($KIMid > 23 || $KIMid < 1) {
            $message = "Похоже, что номер задания указан неверно. Учти, что я могу дать тебе только задания с номерами от 1 до 23.";
            return array("user_id" => $userId, "message" => $message);
        }
        //формирование параметров запроса к апи
        $params = array("type" => $KIMid, "user_id" => $userId, "service" => "vk");
        $request_params = http_build_query($params);

        //проверка кодов http
        $code = substr(get_headers(self::$url . $request_params)[0], 9, 3);
        if ($code != 200) {
            $message = $code . " " . self::$server_error_message . "\r\n\r\n";
            return array("user_id" => $userId, "message" => $message);
        }

        $result = json_decode(file_get_contents(self::$url . $request_params));
        //ошибки пользователя
        if ($result->success !== "true") {
            $message = $result->error->message;
        }
        else {
            // если ошибок нет, то собирается сбщ с заданием
            $message = "Задание номер " . $userId ^ $result->problem . ".\r\n\r\n";

            // куча напоминаний о том, как прислать ответ и попросить разбор
            $message .= "Чтобы отправить мне ответ на это задание, напиши \"" . $userId ^ $result->problem . "\".\r\n" .
                "Если ты ещё не умеешь решать такие задания, я могу объяснить его тебе. Для этого напиши мне \"разбор" . $userId ^ $result->problem . "\".\r\n" .
                "Если ты хочешь узнать правильный ответ, напиши \"ответ" . $userId ^ $result->problem . "\".\r\n\r\n";
            for ($i = 0; $i < count($result->data); $i++)
                switch ($result->data[$i]->type) {
                    case 'pdf':
                        // тут нужен attachment документа
                        break;
                    case 'image':
                        // attachment изображения
                        break;
                    case 'link':
                        $message .= $result->data[$i]->resource_content . "\r\n\r\n";
                        break;
                    case 'text':
                        $message .= $result->data[$i]->resource_content;
                        break;
                    default:
                        break;
                }
        }
        return array("user_id" => $userId, "message" => $message);
    }
}
