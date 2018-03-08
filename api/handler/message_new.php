<?php
/**
 * Created by PhpStorm.
 * User: kurenchuksergey
 * Date: 26.02.18
 * Time: 15:53
 */
include_once "iHandler.php";
include_once "message/help.php";
include_once "message/Test.php";

class message_new implements iHandler
{
    public static function run ( $data )
    {
        //\api\Api::messageSend(\help::run($data));
        $user_message = $data->object->body;
        $user_message = explode(' ', $user_message);
        //приведение к нижнему регистру \
        switch ( $user_message[0] )
        {
            case 'помощь':
                \api\Api::messageSend ( \help::run ( $data ) );
                break;
            case 'темы':
                // обращение к интерфейсу бд
                break;
            case 'задание':
                //класс, разбирающийся с заданийми. передать user_message[1]
                break;
            case 'статистика':
                //обращение к интерфейсу бд
                break;
            case 'разбор':
                //обращение к интерфейсу бд, передать user_message[1]
                break;
            default:
                if (preg_match("/^\d+$/", $user_message[0]))
                    //$user_message[1] - ответ
                    //модуль проверки ответа, передать user_message[1]
                    \api\Api::messageSend ( \test::run ( $data ) );
                else
                    \api\Api::messageSend ( \help::run ( $data ) );
                break;
        }
    }
}
    