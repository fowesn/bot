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
    public static function getTasksStatistics($userID)
    {
        $message = $userID;
        return array("user_id" => $userID, "message" => $message);
    }

    public static function getTaskStatistics($userID, $taskID)
    {
        $message = $userID . ' ' . $taskID;
        return array("user_id" => $userID, "message" => $message);
    }

}