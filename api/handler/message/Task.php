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
        $message ="";
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getThemeTaskMessage($userId, $theme) {
        $message ="";
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getKIMTaskMessage($userId, $KIMid) {
        $message ="";
        return array("user_id" => $userId, "message" => $message);
    }
}