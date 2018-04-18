<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 15.02.18
 * Time: 21:47
 */

namespace project\request;


class Test implements iRequest {
	private $request_params;

	public function __construct($data) {

		//$userInfo = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$data->object->user_id}&v=" . VERSION_VK_API));

		//и извлекаем из ответа его имя
		//$user_name = $userInfo->response[0]->first_name;


// Формируем объект oFile содержащий файл
		$file = curl_file_create("image.png", 'image/png', 'filename.png');

// Форми


		$peer_id = $data->object->user_id;
		$serverUploadInfo = json_decode(file_get_contents("https://api.vk.com/method/photos.getMessagesUploadServer?peer_id=" . $peer_id .
			"&access_token=" . COMMUNITY_TOKEN));

		echo var_dump($serverUploadInfo);
		echo "------------------------------\n\n<br>";

		$upload_url = $serverUploadInfo->response->upload_url;
		/*  $ch = curl_init();
		   curl_setopt_array($ch, array(
			   CURLOPT_URL => $upload_url,
			   CURLOPT_RETURNTRANSFER => true,
			   CURLOPT_POST => true,
			   CURLOPT_POSTFIELDS => array('photo'=>$file))
		   );
		   curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));

		   $out = curl_exec($ch);
		   curl_close($ch);
		   echo "\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\<br><br>";
		   echo "\n\n";
		*/
		$ch = curl_init();
		curl_setopt_array($ch, array(
				CURLOPT_URL => $upload_url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => array('photo' => getImage("Петя")))
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));

		$out = curl_exec($ch);
		curl_close($ch);
		echo "\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\<br><br>";
		echo "\n\n";


		$out = json_decode($out);
		echo var_dump($out);
		$server = $out->server;
		$hash = $out->hash;
		$photo = $out->photo;

		echo "----------------------------\n<br><br>";
		$uploadPhoto = json_decode(file_get_contents("https://api.vk.com/method/photos.saveMessagesPhoto?access_token=" . COMMUNITY_TOKEN .
			"&photo=${photo}&server=$server&hash=$hash"));

		echo var_dump($uploadPhoto);
		$this->request_params = array(
			'message' => "Вау,еб..",
			'user_id' => $data->object->user_id,
			'access_token' => COMMUNITY_TOKEN,
			'v' => VERSION_VK_API,
			"attachment" => $uploadPhoto->response[0]->id
			//"attachment" => "photo-66270811_456239907"
		);

	}

	public function getResult() {
		return $this->request_params;
	}
}