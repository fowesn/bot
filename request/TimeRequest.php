<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 14.02.18
 * Time: 19:16
 */

namespace project\request;


class TimeRequest implements iRequest
{
    private $request_params;

    public function __construct($data)
    {
        $date = date("H:i:s");
        $this->request_params = array(
            'message' => "Сейчас {$date}",
            'user_id' => $data->object->user_id,
            'access_token' => COMMUNITY_TOKEN,
            'v' => VERSION_VK_API
        );
    }

    public function getResult()
    {
        return $this->request_params;
        
    }
}