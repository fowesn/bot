<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 17:48
 */

namespace MainModule\handler;

use MainModule\VKAPI;
use MainModule\handler\message as message;
class message_new
{

    /**
     * @param $data
     * @throws \Exception
     */
    public static function chooseAnswer($data)
    {
        $user_message = self::parse($data->object->body);
        //приведение к нижнему регистру
        /*$user_message = mb_strtolower($user_message, 'UTF-8');
        //удаление из массива кавычек, угловых скобок, точек, запятых, если пользователь случайно их поставил
        //$search = array('\"', '<', '>', ',', '.');
        $user_message = str_replace("\"", "", $user_message);
        $user_message = str_replace("\'", "", $user_message);
        $user_message = str_replace("<", "", $user_message);
        $user_message = str_replace(">", "", $user_message);
        $user_message = str_replace(",", "", $user_message);
        $user_message = str_replace(".", "", $user_message);
        //разделение сообщения пользователя на массив слов
        $user_message = explode(' ', $user_message);*/

        switch ($user_message[0])
        {
            case 'фото':
				$result = VKAPI::pictureAttachmentMessageSend($data->object->user_id, 'https://www.google.ru/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');

//				$result = \MainModule\VKAPI::documentAttachmentMessageSend($data->object->user_id,"https://www.cryptopro.ru/sites/default/files/products/pdf/files/CryptoProPDF_UserGuide.pdf");
				VKAPI::messageSend(array("user_id" => $data->object->user_id, "message" => "смотри че могу", "attachment" => $result));
                break;
            case 'помощь':
                if(count($user_message) > 1)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::help()));
                else {
					$message = message\OtherRequests::GetHelpMessage();
                    for($i = 0; $i < count($message); $i++)
						VKAPI::messageSend(array("user_id" => $data->object->user_id,
                                                    "message" => $message[$i]));
                }
                break;
            case 'темы':
                if(count($user_message) > 1)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::themes()));
                else
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => self::deleteUnderscore(message\OtherRequests::getThemesList())));
                break;
            case 'задание':
                if(count($user_message) == 2 && preg_match("/^\d+$/", $user_message[1]))
                    VKAPI::messageSend(message\Task::getKIMTaskMessage($data->object->user_id, $user_message[1]));
                else if (count($user_message) == 1)
                    VKAPI::messageSend(message\Task::getRandomTaskMessage($data->object->user_id));
                else
                    VKAPI::messageSend(message\Task::getThemeTaskMessage($data->object->user_id, self::setUnderscore($user_message)));
                break;
            case 'разбор':
                if(count($user_message) != 2)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::anasysis()));
                else
					VKAPI::messageSend(message\Answer::getAnalysis($data->object->user_id, $user_message[1]));
                break;
            case 'ресурсы':
                if(count($user_message) > 1)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::resources()));
                else
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\OtherRequests::getResourceTypesList()));
                break;
            case 'ресурс':
                if(count($user_message) != 2)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::resource()));
                else
					VKAPI::messageSend(message\OtherRequests::setUserPreferredResource($data->object->user_id, $user_message[1]));
                break;
            case 'ответ':
                if(count($user_message) != 2)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::answer()));
                else
					VKAPI::messageSend(message\Answer::getAnswer($data->object->user_id, $user_message[1]));
                break;
            case 'привет':
				VKAPI::messageSend(array("user_id" => $data->object->user_id,
					"message" => message\UnidentifiedPartialRequests::hello()));
                break;
			case "тест":
				//$result = VKAPI::pictureAttachmentMessageSend($data->object->user_id, 'https://www.google.ru/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');
                $reflector = new \ReflectionClass('message_new');
                $result = $reflector->getFileName() . $reflector->getStartLine();

                VKAPI::messageSend(array("user_id" => $data->object->user_id,
				//"message" => file_get_contents("http://kappa.cs.petrsu.ru/~omelchen/vk/bot/lotoftext"),"attachment" => $result));
                "message" => $result));
				break;
            default:
                if (preg_match("/^\d+$/", $user_message[0])) {
                    if (count($user_message) != 2)
						VKAPI::messageSend(array("user_id" => $data->object->user_id,
							"message" => message\UnidentifiedPartialRequests::check()));
                    else
						VKAPI::messageSend(message\Answer::checkUserAnswer($data->object->user_id, $user_message[0], $user_message[1]));
                }
                else
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\OtherRequests::getBasicMessage()));
                break;
        }
    }

    private static function parse($user_message) {
        //приведение к нижнему регистру
        $user_message = mb_strtolower($user_message, 'UTF-8');
        //удаление из массива кавычек, угловых скобок, точек, запятых, если пользователь случайно их поставил
        $search = array("\"", "\'", "<", ">", ",", ".");
        $user_message = str_replace($search, "", $user_message);
        //разделение сообщения пользователя на массив слов
        $user_message = explode(' ', $user_message);
        return $user_message;
    }
    private static function setUnderscore($theme)
    {
        array_shift($theme);
        $theme = implode("_", $theme);
        return $theme;
    }
    private static function deleteUnderscore($text)
    {
        $text = str_replace("_", " ", $text);
        return $text;
    }
}
