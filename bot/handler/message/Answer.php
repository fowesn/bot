<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */

class Answer
{
    private static $url = 'http://kappa.cs.petrsu.ru/~nestulov/API/public/index.php/problems/';
    public static function getAnswer($userId, $taskId) {
        $message = "функция getAnswer, пользователь " . $userId . ", номер задания " . $taskId;
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getAnalysis($userId, $taskId) {
        $message = "функция getAnalysis, пользователь " . $userId . ", номер задания " . $taskId;;
        return array("user_id" => $userId, "message" => $message);
    }
    public static function checkUserAnswer($userId, $taskId, $answer)
    {
        // post
        $message = "";
        return array("user_id" => $userId, "message" => $message);

    }
}