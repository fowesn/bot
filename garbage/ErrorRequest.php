<?php namespace project\request;


/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 14.02.18
 * Time: 19:21
 */


class ErrorRequest implements iRequest {
	private $request_params;

	public function __construct($user_id) {
		$userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=" . VERSION_VK_API));

		//и извлекаем из ответа его имя
		$user_name = $userInfo->response[0]->first_name;

		//С помощью messages.send и токена сообщества отправляем ответное сообщение

		$this->request_params = array(
			'message' => "Привет, {$user_name}!<br>" .
				"Если хочешь узнать, что я могу, напиши \"help me\" или \"нужна помощь\"",
			'user_id' => $user_id,
			'access_token' => COMMUNITY_TOKEN,
			'v' => VERSION_VK_API
		);
	}

	public function getResult() {

		return $this->request_params;
	}
}