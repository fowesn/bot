<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Time: 17:48
 */
include_once "message/Task.php";
include_once "message/Answer.php";
include_once "message/OtherRequests.php";
class message_new
{
    /**
     * @param $data
     * @throws Exception
     * @throws \api\RequestError
     */

    public static function run ( $data )
    {
        //\api\Api::messageSend(\help::run($data));
        $user_message = $data->object->body;
        $user_message = explode(' ', $user_message);
        //приведение к нижнему регистру \
        switch ( $user_message[0] )
        {
            case 'помощь':
                \api\Api::messageSend ( \OtherRequests::GetHelpMessage ($data->object->user_id));
                break;
            case 'темы':
                \api\Api::messageSend( \OtherRequests::getThemesList($data->object->user_id));
                break;
            case 'задание':
                //класс, разбирающийся с заданийми. передать user_message[1]
                if(count($user_message) == 2) {
                    if (preg_match("/^\d+$/", $user_message[1]))
                        \api\Api::messageSend(\Task::getKIMTaskMessage($data->object->user_id, $user_message[1]));
                    else
                        \api\Api::messageSend(\Task::getThemeTaskMessage($data->object->user_id, $user_message[1]));
                }
                else
                    \api\Api::messageSend(\Task::getRandomTaskMessage($data->object->user_id));
                break;
            case 'разбор':
                \api\Api::messageSend(\Answer::getAnalysis($data->object->user_id, $user_message[1]));
                break;
            case 'ресурсы':
                \api\Api::messageSend(\OtherRequests::getResourceTypesList($data->object->user_id));
                break;
            case 'ресурс':
                \api\Api::messageSend(\OtherRequests::setUserPreferredResource($data->object->user_id, $user_message[1]));
                break;
            case 'ответ':
                \api\Api::messageSend ( \Answer::getAnswer($data->object->user_id, $user_message[1]));
                break;
            default:
                if (preg_match("/^\d+$/", $user_message[0]))
                    //$user_message[1] - ответ
                    //модуль проверки ответа, передать user_message[1]
                    ;
                else
                    \api\Api::messageSend ( \OtherRequests::getBasicMessage($data->object->user_id));
                break;
        }
    }
}
    