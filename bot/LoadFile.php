<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 11.05.18
 * Time: 0:00
 */

namespace api;


class LoadFile {
	static public function getImage($url){
		ini_set("allow_url_fopen",true);
		/** @var  $type формат изображения */
		$type = exif_imagetype($url);
		if($type == false)
			throw new Exception(__FILE__." : ".__LINE__." File not found ".$url);
		/** @var  $mimeType string mime тип изображения для запроса */
		$mimeType = image_type_to_mime_type($type);
		ob_start();
		ob_clean();
		echo "--9e99e84655473cf6\r\n";
		echo "Content-Disposition: form-data; name=\"photo\"; filename=\"filename.png\"\r\n";
		echo "Content-Type: ".$mimeType."\r\n";
		echo 'Content-Transfer-Encoding: binary'."\r\n\r\n";
//		header('Content-Type: '.$mimeType);
//		header('Content-Disposition: attachment; filename=' . basename($url));
//		header('Content-Length: ' . filesize($url));

//		readfile($url);
		if ($fd = fopen($url, 'rb')) {
			while (!feof($fd)) {
				print fread($fd, 1024);
			}
			fclose($fd);
		}
		echo "\r\n--9e99e84655473cf6--";
		$out = ob_get_contents();
		ob_end_clean();
		return $out;

	}

}