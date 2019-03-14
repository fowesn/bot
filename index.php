<?php

namespace project;

use api\CallbackApi;
use api\EventNotSupported;
use api\RequestError;
use api\SecurityBreach;

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
			throw new \Exception ('File ' . $fileFullName . ' not found!');
		}

		require_once($fileFullName);

		if (!class_exists($className)) {
			throw new \Exception ('Class ' . $className . ' not found!');
		}
	}
);

/////////////////////////////////////////////////

$data = json_decode(file_get_contents('php://input'));


try {

	CallbackApi::requestHandler($data);

} catch (SecurityBreach $err) {
	//echo $err->getMessage();
	file_put_contents(LOG, $err->getMessage() ." ".$err->getCode(). "\r\n", FILE_APPEND);

} catch (EventNotSupported $err) {
    //echo $err->getMessage();
	file_put_contents(LOG, $err->getMessage() ." ".$err->getCode(). "\r\n", FILE_APPEND);

} catch (RequestError $err){
    //echo $err->getMessage();
    file_put_contents(LOG, $err->getMessage() ." ".$err->getCode(). "\r\n", FILE_APPEND);

} catch (\Exception $err) {
    //echo $err->getMessage();
    file_put_contents(LOG, $err->getMessage() ." ".$err->getCode(). "\r\n", FILE_APPEND);
} finally {
	echo "ok";
}
?>

