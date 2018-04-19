<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */

class Answer
{
    public static function getAnswer($userId, $taskId) {
        if (!preg_match("/^\d+$/", $taskId))
            $message = "Неверный номер задания. Проверь, нет ли там ошибки";
        else
            $message = "функция getAnswer, пользователь " . $userId . ", номер задания " . $taskId;
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getAnalysis($userId, $taskId) {
        $message = "функция getAnalysis, пользователь " . $userId . ", номер задания " . $taskId;;
        return array("user_id" => $userId, "message" => $message);
    }
}