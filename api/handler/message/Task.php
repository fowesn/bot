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
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getThemeTaskMessage($userId, $theme) {
        $message = "функция getThemeTaskMessage, пользователь " . $userId . ", тема " . $theme;
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getKIMTaskMessage($userId, $KIMid) {
        if($KIMid > 23 || $KIMid < 1)
            $message = "Похоже, что номер задания указан неверно. Учти, что я могу дать тебе только задания с номерами от 1 до 23";
        else
            $message = "функция getKIMTaskMessage, пользователь " . $userId . ", номер в киме " . $KIMid;
        return array("user_id" => $userId, "message" => $message);
    }
}