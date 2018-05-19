<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 17:48
 */

namespace api\handler;

use api\Api;
use api\handler\message as message;
use api\RequestError;

class message_new
{
    /**
     * @param $data
     * @throws RequestError
     * @throws \Exception
     * @throws message\Exception
     */

    public static function run ( $data )
    {
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

        switch ( $user_message[0] )
        {
            case 'фото':
				$result = Api::pictureAttachmentMessageSend($data->object->user_id, 'https://www.google.ru/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');

//				$result = \api\Api::documentAttachmentMessageSend($data->object->user_id,"https://www.cryptopro.ru/sites/default/files/products/pdf/files/CryptoProPDF_UserGuide.pdf");
				Api::messageSend(array("user_id" => $data->object->user_id, "message" => "смотри че могу", "attachment" => $result));
                break;
            case 'помощь':
                if(count($user_message) > 1)
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::help()));
                else {
					$message = message\OtherRequests::GetHelpMessage();
                    for($i = 0; $i < count($message); $i++)
						Api::messageSend(array("user_id" => $data->object->user_id,
                                                    "message" => $message[$i]));
                }
                break;
            case 'темы':
                if(count($user_message) > 1)
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::themes()));
                else
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\OtherRequests::getThemesList()));
                break;
            case 'задание':
                if(count($user_message) > 2)
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::tasks()));
                else if(count($user_message) == 2) {
                    if (preg_match("/^\d+$/", $user_message[1]))
						Api::messageSend(message\Task::getKIMTaskMessage($data->object->user_id, $user_message[1]));
                    else
						Api::messageSend(message\Task::getThemeTaskMessage($data->object->user_id, $user_message[1]));
				} else Api::messageSend(message\Task::getRandomTaskMessage($data->object->user_id));
                break;
            case 'разбор':
                if(count($user_message) != 2)
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::anasysis()));
                else
					Api::messageSend(message\Answer::getAnalysis($data->object->user_id, $user_message[1]));
                break;
            case 'ресурсы':
                if(count($user_message) > 1)
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::resources()));
                else
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\OtherRequests::getResourceTypesList()));
                break;
            case 'ресурс':
                if(count($user_message) != 2)
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::resource()));
                else
					Api::messageSend(message\OtherRequests::setUserPreferredResource($data->object->user_id, $user_message[1]));
                break;
            case 'ответ':
                if(count($user_message) != 2)
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\UnidentifiedPartialRequests::answer()));
                else
					Api::messageSend(message\Answer::getAnswer($data->object->user_id, $user_message[1]));
                break;
            case 'привет':
				Api::messageSend(array("user_id" => $data->object->user_id,
					"message" => message\UnidentifiedPartialRequests::hello()));
                break;
            default:
                if (preg_match("/^\d+$/", $user_message[0])) {
                    if (count($user_message) != 2)
						Api::messageSend(array("user_id" => $data->object->user_id,
							"message" => message\UnidentifiedPartialRequests::check()));
                    else
						Api::messageSend(message\Answer::checkUserAnswer($data->object->user_id, $user_message[0], $user_message[1]));
                }
                else
					Api::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\OtherRequests::getBasicMessage()));
                break;
        }
    }
}
