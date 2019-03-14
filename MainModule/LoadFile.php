<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 11.05.18
 * Time: 0:00
 */

namespace MainModule;


class LoadFile {
	/**
	 * @param $photo_path string путь к фотографий
	 * @return array
	 * @throws \Exception
	 */
	static private function getImage($photo_path) {
		ini_set("allow_url_fopen",true);
		/** @var  $type string - формат изображения */
		$type = exif_imagetype($photo_path);
		if($type == false)
			throw new \Exception(__FILE__ . " : " . __LINE__ . " File not found or not Image" . $photo_path);
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
	private static function getDocument($document_path) {
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
	 * @return string
	 * @throws \Exception
	 */
	private static function sendData($server, $data) {
		/** https */
		$fp = fsockopen("ssl://" . $server["host"], 443, $errno, $errstr, 5);
		if (!$fp)
			throw new RequestError(__FILE__ . ":" . __LINE__ . "проблемы с сокетом" . $server["host"]);
		/** @var string разделитель полей на сокете $boundary */
		$boundary = md5(uniqid(time()));
		/** подготовка контента */
		$content = "--" . $boundary . "\r\n";
		$content .= 'Content-Disposition: form-data; name="' . $data['fieldName'] . '"; filename="' . $data['fileName'] . '"'."\r\n";
		$content .= "Content-Type: " . $data['mime'] . "\r\n";
		$content .= 'Content-Transfer-Encoding: binary' . "\r\n\r\n";
		$content .= $data['content'] . "\r\n";
		$content .= "--" . $boundary . '--';

		fwrite($fp, 'POST ' . $server["path"] . '?' . $server["query"] . ' HTTP/1.1' . "\r\n");
		fwrite($fp, 'Host: ' . $server["host"] . " \r\n");
		fwrite($fp, 'Content-Type: multipart/form-data; boundary=' . $boundary . "\r\n");
		fwrite($fp, 'Content-Length: ' . strlen($content) . "\r\n\r\n");
		fwrite($fp, $content);
		$result = '';
		stream_set_timeout($fp,1);
		while (strlen($buf = fread($fp, 10))>0)
		{
			$result .=$buf;
		}
		// закрываем соединение
		fclose($fp);

		return $result;
	}
	///////////////////////ФАСАД////////////////////
	/**
	 * @param $server array - url parse
	 * @param $document_path string
	 * @return string http response
	 * @throws \Exception - отсутсвие файла
	 */
	public static function sendDocument($server, $document_path){
		return self::sendData($server,self::getDocument($document_path));
	}

	/**
	 * @param $server array - url parse
	 * @param $photo_path string - url to file
	 * @return string http response
	 * @throws \Exception
	 */
	public static function sendImage($server, $photo_path){
		return self::sendData($server,self::getImage($photo_path));
	}
}