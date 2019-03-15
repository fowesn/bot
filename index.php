<?php

namespace project;

use MainModule\RequestHandler;
if (!isset($_REQUEST)) {
    return;
}
//////////////////////////////////////////////////
/*
 * Подключение модулей
 */
include_once("settings.php");

//////////////AutoLoader//////////////////////////

spl_autoload_register
(
	function ($className) {
		$siteRoot = dirname(__FILE__);

		$classFullName = ltrim
		(
			implode
			(
				DIRECTORY_SEPARATOR,
				explode
				(
					'\\',
					$className
				)
			),
			DIRECTORY_SEPARATOR
		);

		$fileFullName = $siteRoot . DIRECTORY_SEPARATOR . $classFullName . '.php';

		if (!file_exists($fileFullName)) {
			throw new \Exception (__FILE__ . " : " . __LINE__ . 'File ' . $fileFullName . ' not found!');
		}

		require_once($fileFullName);

		if (!class_exists($className)) {
			throw new \Exception (__FILE__ . " : " . __LINE__ . 'Class ' . $className . ' not found!');
		}
	}
);

/////////////////////////////////////////////////

$data = json_decode(file_get_contents('php://input'));


try {
	RequestHandler::requestHandler($data);
} catch (\Exception $err) {
    //echo $err->getMessage();
    file_put_contents(LOG, $err->getMessage() ." ".$err->getCode(). "\r\n", FILE_APPEND);
} finally {
	echo "ok";
}
?>

