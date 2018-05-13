<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 11.05.18
 * Time: 0:00
 */

namespace api;


class LoadFile {
	/**
	 * @param $photo_path string путь к фотографий
	 * @return array
	 * @throws \Exception
	 */
	static public function getImage($photo_path) {
		ini_set("allow_url_fopen",true);
		/** @var  $type string - формат изображения */
		$type = exif_imagetype($photo_path);
		if($type == false)
			throw new \Exception(__FILE__ . " : " . __LINE__ . " File not found " . $photo_path);
		/** @var  $mimeType string mime тип изображения для запроса */
		$mimeType = image_type_to_mime_type($type);

		ob_start();
		ob_clean();
		if ($fd = fopen($photo_path, 'rb')) {
			while (!feof($fd)) {
				print fread($fd, 1024);
			}
			fclose($fd);
		}
		$out = ob_get_contents();
		ob_end_clean();
		/** @var array $result */
		$result = array("mime" => $mimeType,
			"fieldName" => "photo",
			"fileName" => basename($photo_path),
			"content" => $out);
		return $result;
	}


	/**
	 * @param $document_path - url к документу
	 * @return array - документ
	 * @throws \Exception - в случае отсутвие файла
	 */
	public static function getDocument($document_path) {
		ini_set("allow_url_fopen", true);
		if (!fopen($document_path, "r"))
			throw new \Exception(__FILE__ . " : " . __LINE__ . "File not found " . $document_path);
		$mimeType = mime_content_type($document_path);
		ob_start();
		ob_clean();
		if ($fd = fopen($document_path, 'rb')) {
			while (!feof($fd)) {
				print fread($fd, 1024);
			}
			fclose($fd);
		}
		$out = ob_get_contents();
		ob_end_clean();
		$result = array("mime" => $mimeType,
			"fieldName" => "file",
			"fileName" => basename($document_path),
			"content" => $out);
		return $result;

	}

	/**
	 * @param $server array
	 * @param $data array
	 * @throws \Exception
	 */
	public static function sendData($server, $data) {
		/** https */
		$fp = fsockopen("ssl://" . $server["host"], 443, $errno, $errstr, 5);
		if (!$fp)
			throw new RequestError(__FILE__ . ":" . __LINE__ . "проблемы с сокетом" . $server["host"]);
		/** @var string разделитель полей на сокете $boundary */
		$boundary = md5(uniqid(time()));
		/** подготовка контента */
		$content = "--" . $boundary . '\r\n';
		$content .= 'Content-Disposition: form-data; name=\"' . $data['fieldName'] . '\"; filename=\"' . $data['fileName'] . '\"\r\n';
		$content .= "Content-Type: " . $data['mime'] . "\r\n";
		$content .= 'Content-Transfer-Encoding: binary' . "\r\n\r\n";
		$content .= $data['content'] . '\r\n';
		$content .= "--" . $boundary . '--';

		fwrite($fp, 'POST ' . $server["path"] . '?' . $server["query"] . ' HTTP/1.1' . "\r\n");
		fwrite($fp, 'Host: ' . $server["host"] . " \r\n");
		fwrite($fp, 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n");
		fwrite($fp, 'Content-Length: ' . strlen($content) . "\r\n\r\n");
		fwrite($fp, $content);
		$result = '';
		while (!feof($fp)) $result .= fgets($fp, 1024);
		// закрываем соединение
		fclose($fp);
		return $result;
	}

}