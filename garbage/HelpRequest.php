<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 14.02.18
 * Time: 19:08
 */

namespace project\request;

class HelpRequest implements iRequest {

	private $request_params;

	public function __construct($user_id) {

		$this->request_params = array(
			'message' => "Пока что я могу мало, но учусь!<br>" .
				"Напиши \"время\", и я скажу тебе, который час",
			'user_id' => $user_id,
			'access_token' => COMMUNITY_TOKEN,
			'v' => VERSION_VK_API
		);
	}

	public function getResult() {
		return $this->request_params;
	}
}