<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey, fowesn
 * Date: 19.04.18
 * Time: 17:42
 */

namespace vkApi\handler;


class message_new {

	/**
	 * @var array массив обработчиков,в формате "событие"=>"имя_обработчика" (без расширения, файлы должны находиться в папке message)
	 *
	 */
	public static $handlers = array(
		"помощь" => "help",
		'темы' => "themes",
		'задание' => "task",
		'разбор' => "anasysis",
		"ресурсы" => "resources",
		"ресурс" => "resource",
		"ответ" => "answer",
		"default" => "byDefault"
	);

	/**
	 * Данная функция парсит сообщение пользователя и управляет потоком исполнения
	 * @param $data - массив от вк
	 * @throws \Exception - в случаях,когда нет файла обработчика
	 */
	public static function run ( $data ) {
		//проверка наличия обязательного обработчика
		if(!self::testHandlers("default") || !file_exists(__DIR__."/message/".self::$handlers["default"].".php")) {
			throw new \Exception("Массив событий некорректен либо нет файла default. Файл ".__FILE__." ::".__LINE__);
		}


		$user_message = $data->object->body;
		//приведение к нижнему регистру
		$user_message = mb_strtolower($user_message, 'UTF-8');
		//удаление из массива кавычек, угловых скобок, точек, запятых, если пользователь случайно их поставил
		//$search = array('\"', '<', '>', ',', '.');
		$user_message = str_replace("\"", "", $user_message);
		$user_message = str_replace("\'", "", $user_message);
		$user_message = str_replace("<", "", $user_message);
		$user_message = str_replace(">", "", $user_message);
		$user_message = str_replace(",", "", $user_message);
		$user_message = str_replace(".", "", $user_message);
		//разделение сообщения пользователя на массив слов
		$user_message = explode(' ', $user_message);


		//Получение обработчика
		$action = "default";
		if(self::testHandlers($user_message[0])){
			$action = $user_message[0];
		}
		//вызов обработчика
		if(class_exists("\\vkApi\\handler\\message\\".self::$handlers[$action],true))
		{
			call_user_func_array("\\vkApi\\handler\\message\\".self::$handlers[$action]."::run",array($user_message,$data));
		}else{
			throw new \Exception("Нет обработчика события".self::$handlers[$action]." в ".__FILE__." ::".__LINE__);
		}

	}

	/**
	 * Функция проверяет наличие описание обработчика в массиве $handler
	 * @param $value - ключевое слово(событие)
	 * @return bool - наличие обработчика
	 */
	private static function testHandlers($value){
		if(!isset(self::$handlers[$value]))
			return false;
		return true;
	}
}