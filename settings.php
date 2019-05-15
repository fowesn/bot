<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 14.02.18
 * Time: 15:42
 */

//Константа для провеки включения файла в общий контейнер
define("INCLUDED", true);

/*
 * Константы для работы с vk api
 */
// строка для подтверждения адреса сервера из настроек Callback API
define("CONFIRMATION_TOKEN", "02e5341a");
// ключ доступа сообщества - для обращения к API от имени сообщества
define("COMMUNITY_TOKEN", "2e0c1c240cd3cbae9542b68e1c647a47759f5277f298c431f579d06924612e5dd89ef80fdde7f4a73afd3");
// Secret key
define("SECRET_KEY", "kappa_tryout_key");
// версия vk api
define("VERSION_VK_API", "5.71");
// адрес сервера с api и бд
define("HOST_API", 'http://kappa.cs.petrsu.ru/~nestulov/API/v1/public/index.php');
// стандартное сообщение при ошибке сервера
define("SERVER_ERROR_MESSAGE", 'Что-то пошло не так. Попробуй снова!');
define("LOG","log.log");
if(!file_exists(LOG)) {
	//echo "tut";
	if (!($fp = fopen(LOG, "w+")))
		echo "error";
	else {
		fwrite($fp, "первая строка Log File");
		fclose($fp);
	}
}
	