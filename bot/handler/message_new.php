<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 17:48
 */
include_once "message/Task.php";
include_once "message/Answer.php";
include_once "message/OtherRequests.php";
include_once "message/UnidentifiedPartialRequests.php";

class message_new
{
    /**
     * @param $data
     * @throws Exception
     * @throws \api\RequestError
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
				$result = \api\Api::pictureAttachmentMessageSend($data->object->user_id,'кот.png');
				$photo = "photo".$result->owner_id."_".$result->id;
//				throw new \api\RequestError($photo);
                \api\Api::messageSend(array("user_id" => $data->object->user_id, "message" => "смотри че могу","attachment" => $photo));

                break;
            case 'помощь':
                if(count($user_message) > 1)
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \UnidentifiedPartialRequests::help()));
                else {
                    $message = \OtherRequests::GetHelpMessage();
                    for($i = 0; $i < count($message); $i++)
                        \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                    "message" => $message[$i]));
                }
                break;
            case 'темы':
                if(count($user_message) > 1)
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                        "message" => \UnidentifiedPartialRequests::themes()));
                else
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \OtherRequests::getThemesList()));
                break;
            case 'задание':
                if(count($user_message) > 2)
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \UnidentifiedPartialRequests::tasks()));
                else if(count($user_message) == 2) {
                    if (preg_match("/^\d+$/", $user_message[1]))
                        \api\Api::messageSend(\Task::getKIMTaskMessage($data->object->user_id, $user_message[1]));
                    else
                        \api\Api::messageSend(\Task::getThemeTaskMessage($data->object->user_id, $user_message[1]));
                }
                else \api\Api::messageSend(\Task::getRandomTaskMessage($data->object->user_id));
                break;
            case 'разбор':
                if(count($user_message) != 2)
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \UnidentifiedPartialRequests::anasysis()));
                else
                    \api\Api::messageSend(\Answer::getAnalysis($data->object->user_id, $user_message[1]));
                break;
            case 'ресурсы':
                if(count($user_message) > 1)
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \UnidentifiedPartialRequests::resources()));
                else
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \OtherRequests::getResourceTypesList()));
                break;
            case 'ресурс':
                if(count($user_message) != 2)
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \UnidentifiedPartialRequests::resource()));
                else
                    \api\Api::messageSend(\OtherRequests::setUserPreferredResource($data->object->user_id, $user_message[1]));
                break;
            case 'ответ':
                if(count($user_message) != 2)
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \UnidentifiedPartialRequests::answer()));
                else
                    \api\Api::messageSend(\Answer::getAnswer($data->object->user_id, $user_message[1]));
                break;
            default:
                if (preg_match("/^\d+$/", $user_message[0]))
                    //$user_message[1] - ответ
                    //модуль проверки ответа, передать user_message[1]
                    ;
                else
                    \api\Api::messageSend(array("user_id" => $data->object->user_id,
                                                "message" => \OtherRequests::getBasicMessage()));
                break;
        }
    }
}
