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

    /**
     * @param $userID
     * @return array
     * @throws \Exception
     */
    public static function getTasksStatistics($userID)
    {
        if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");

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

    /**
     * @param $userID
     * @param $taskID
     * @return string
     * @throws \Exception
     */
    public static function getTaskStatistics($userID, $taskID)
    {
        if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");

        $url = HOST_API . '/assignments?' . http_build_query(array("user" => $userID, "problem" => $taskID, "service" => 'vk'));

        $code = substr(get_headers($url)[0], 9, 3);

        if($code == 200)
        {
            $result = json_decode(file_get_contents($url));
            if($result->status === "a1-0")
                $message = "Задание с таким номером я тебе не выдавал. Чтобы посмотреть список номеров нерешённых тобой заданий, напиши мне \"задания\"";
            else
                $message = "Задание номер " . $taskID . ".\r\n
                            Тема: " . $result->data->problem_type . "\r\n
                            Год задания: " . $result->data->year . "\r\n
                            Номер в КИМе: " . $result->data->exam_item_number . "\r\n
                            Количество данных ответов: " . $result->data->answers_provided . "\r\n
                            На это задание " . ($result->data->correct_answer_provided ? "был " : "не был ") . "дан правильный ответ\r\n
                            На это задание " . ($result->data->correct_answer_requested ? "был " : "не был ") . "запрошен правильный ответ";
        }
    else
            $message = $code . ". " . self::$server_error_message;
        return $message;
    }

}