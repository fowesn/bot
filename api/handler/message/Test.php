<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 08.03.18
 * Time: 16:46
 */

class test implements iHandler {

    public static function run($data) {
        $message = "Ты воняешь рыбой";
        return array("user_id" => $data->object->user_id, "message" => $message);
    }
}