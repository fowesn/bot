<?php
/**
 * Created by PhpStorm.
 * User: fow
 * Date: 14.05.2019
 * Time: 21:06
 */

namespace MainModule\handler\message;
class Statistics
{
    private static $server_error_message = "Что-то пошло не так. Попробуй снова!";
    public static function getTasksStatistics($userID)
    {

        $url = HOST_API . '/assignments?' . http_build_query(array("user" => $userID, "service" => 'vk'));

        $code = substr(get_headers($url)[0], 9, 3);

        if($code == 200)
        {
            $result = json_decode(file_get_contents($url));
            $message = "Количество запрошенных заданий: " . $result->data->overall . "\r\n
                        Количество решенных заданий: " . $result->data->solved . "\r\n
                        Количество заданий, на которые был запрошен правильный ответ: " . $result->data->answer_requested . "\r\n
                        Количество нерешённых заданий: " . $result->data->unsolved;

        }
        else
            $message = $code . ". " . self::$server_error_message;
        return array("user_id" => $userID, "message" => $message);
    }

    public static function getTaskStatistics($userID, $taskID)
    {
        $message = $userID . ' ' . $taskID;
        return array("user_id" => $userID, "message" => $message);
    }

}