<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */

class Task
{
    public static function getRandomTaskMessage($userId) {
        $message = "функция getRandomTaskMessage, пользователь " . $userId;
        // get запрос /api/v1/problems/problem
        $params = array("type" => "random", "user_id" => $userId, "service" => "vk");
        $request_params = http_build_query($params);
        $response = json_decode(file_get_contents(HOST_API . "/api/v1/problems/problem?" . $request_params));
        if($response->success === false) {
            //обработка ошибки
            $errorcode = $response->error["code"];
        }
        // куча напоминаний о том, как прислать ответ и попросить разбор
        $message = "";
        for($i = 0; $i < count($response->data); $i++)
            switch ($response->resource_type_code) {
                case 'pdf':
                    // тут нужен attachment документа
                    break;
                case 'image':
                    // attachment изображения
                    break;
                case 'link':
                    $message .= $response->data[$i]->resource_content . "\r\n\r\n";
                    break;
                case 'text':
                    $message .= $response->data[$i]->resource_content . "\r\n\r\n";
                    break;
                default:
                    break;
            }
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getThemeTaskMessage($userId, $theme) {
        $message = "функция getThemeTaskMessage, пользователь " . $userId . ", тема " . $theme;
        // get запрос /api/v1/problems/problem
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getKIMTaskMessage($userId, $KIMid) {
        if($KIMid > 23 || $KIMid < 1) {
            $message = "Похоже, что номер задания указан неверно. Учти, что я могу дать тебе только задания с номерами от 1 до 23";
            return array("user_id" => $userId, "message" => $message);
        }
        $message = "функция getKIMTaskMessage, пользователь " . $userId . ", номер в киме " . $KIMid;
        // get запрос /api/v1/problems/problem
        return array("user_id" => $userId, "message" => $message);
    }
}