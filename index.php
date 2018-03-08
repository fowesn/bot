<?php

namespace project;
//Как я понял, это проверка на то,что скрипт не запушен через shell, ну хотя кто его знает
//вторая догадка, возможно не существует массива $_GET если перейти по ссылки на страницу без параметров

use api\CallbackApi;

if (!isset($_REQUEST)) {
    return;
}
//////////////////////////////////////////////////
/*
 * Подключение модулей
 */
include_once("setting.php");
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
//$data = json_decode(file_get_contents('php://input'));
//не норма
$data = json_decode($_GET['data']);


try {
	CallbackApi::run($data);
	echo "ok";
} catch (\api\SecurityBreach $err) {
	echo $err->getMessage();
	file_put_contents("log.log", $err->getMessage() . "\n", FILE_APPEND);

} catch (\api\EventNotSupported $err) {
	echo $err->getMessage();
	file_put_contents("log.log", $err->getMessage() . "\n", FILE_APPEND);

} catch (\Exception $err) {
	$err->getMessage();
	file_put_contents("log.log", $err->getMessage() . "\n", FILE_APPEND);
}
?>

