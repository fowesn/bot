<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 14.02.18
 * Time: 19:21
 */


namespace project\request;
include_once "iRequest.php";

class ErrorRequest implements iRequest
{
    private $request_params;

    public function __construct($data)
    {
        $userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$data->object->user_id}&v=" . VERSION_VK_API));

        //и извлекаем из ответа его имя
        $user_name = $userInfo->response[0]->first_name;

        //С помощью messages.send и токена сообщества отправляем ответное сообщение

        $this->request_params = array(
            'message' => "Привет, {$user_name}!<br>" .
                "Если хочешь узнать, что я могу, напиши \"help me\" или \"нужна помощь\"",
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