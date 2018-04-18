<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 08.03.18
 * Time: 18:39
 */

namespace database;


//временно. В купе не запустится ибо константы объявленны в setting и внизу сам класс создается 
// адрес сервера
define("DBADDRESS", "localhost");
// логин пользователя
define("DBLOGIN", "root");
// пароль пользователя
define("DBPASS", "");
// название базы данных
define("DBNAME", "bot");

use Exception;

class Task {
	private $mysqli;

	public function __construct() {
		$this->mysqli = new \mysqli(DBADDRESS, DBLOGIN, DBPASS, DBNAME);

		if ($this->mysqli->connect_error) {
			throw new Exception($this->mysqli->connect_error);
		}
	}

	public function getTaskById($task_id) {
		$task_id = $this->mysqli->real_escape_string($task_id);
		$result = $this->mysqli->query("CALL `get_task_by_id` ('$task_id');");
		var_dump($result->fetch_assoc());
	}

//	public static function getTasksByCategory
//
//	public static function getTasksByNumber
//
//	public static function getSolution
//
//	public static function getCategories
//
//	public static function getAnswer
//
//	public static function getScore
//
//	public static function getSolved
//
//	public static function checkAnswer

}

try {
	$var = new Task();
	$var->getTaskById(5);
} catch (Exception $e) {
}