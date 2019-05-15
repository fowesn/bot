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
        $command = array_shift($user_message);
        switch ($command)
        {
            /////////////////////                      ОСНОВНЫЕ ФУНКЦИИ                    /////////////////////


            ///////             Помощь                 ///////
            case 'помощь':
                if(count($user_message) > 0)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\Intelligence::help()));
                else {
					$message = message\OtherRequests::GetHelpMessage();
                    for($i = 0; $i < count($message); $i++)
						VKAPI::messageSend(array("user_id" => $data->object->user_id,
                                                    "message" => $message[$i]));
                }
                break;




            ///////             Темы                 ///////
            case 'темы':
                if(count($user_message) > 0)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\Intelligence::themes()));
                else
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => self::deleteUnderscore(message\OtherRequests::getThemesList())));
                break;




            ///////             Задание                 ///////
            case 'задание':
                if(count($user_message) == 1 && preg_match("/^\d+$/", $user_message[0]))
                    VKAPI::messageSend(message\Task::getKIMTaskMessage($data->object->user_id, $user_message[0]));
                else if (count($user_message) == 0)
                    VKAPI::messageSend(message\Task::getRandomTaskMessage($data->object->user_id));
                else
                    VKAPI::messageSend(message\Task::getThemeTaskMessage($data->object->user_id, self::setUnderscore($user_message)));
                break;




            ///////             Разбор                 ///////
            case 'разбор':
                if(count($user_message) == 1 && preg_match("/^\d+$/", $user_message[0]))
                    VKAPI::messageSend(message\Answer::getAnalysis($data->object->user_id, $user_message[0]));
                else
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\Intelligence::anasysis()));
                break;




            ///////             Ресурсы                 ///////
            case 'ресурсы':
                if(count($user_message) > 0)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\Intelligence::resources()));
                else
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\OtherRequests::getResourceTypesList()));
                break;




            ///////             Ресурс                 ///////
            case 'ресурс':
                if(count($user_message) != 1)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\Intelligence::resource()));
                else
					VKAPI::messageSend(message\OtherRequests::setUserPreferredResource($data->object->user_id, $user_message[1]));
                break;




            ///////             Ответ                 ///////
            case 'ответ':
                if(count($user_message) != 1)
					VKAPI::messageSend(array("user_id" => $data->object->user_id,
						"message" => message\Intelligence::answer()));
                else
					VKAPI::messageSend(message\Answer::getAnswer($data->object->user_id, $user_message[0]));
                break;




            ///////             Задания                 ///////
            case 'задания':
                if(count($user_message) > 0)
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\Intelligence::incompleted()));
                else
                    VKAPI::messageSend(message\Task::getIncompletedTasksList($data->object->user_id));
                break;




            ///////             Год                 ///////
            case 'год':
                if(count($user_message) == 1 && preg_match("/^\d+$/", $user_message[0]))
                    VKAPI::messageSend(message\OtherRequests::setUserPreferredYear($data->object->user_id, $user_message[0]));
                else
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\Intelligence::year()));
                break;




            ///////             Условие                 ///////
            case 'условие':
                if(count($user_message) == 1 && preg_match("/^\d+$/", $user_message[0]))
                    VKAPI::messageSend(message\Task::getTaskAgain($data->object->user_id, $user_message[0]));
                else
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\Intelligence::repeat()));
                break;




            ///////             Статистика                 ///////
            case 'статистика':
                if(count($user_message) == 0)
                    VKAPI::messageSend(message\Statistics::getTasksStatistics($data->object->user_id));
                else if (count($user_message) == 1 && preg_match("/^\d+$/", $user_message[0]))
                    VKAPI::messageSend(message\Statistics::getTaskStatistics($data->object->user_id, $user_message[0]));
                else
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\Intelligence::statistics()));
                break;


            /////////////////////                      ИНТЕЛЛЕКТ                    /////////////////////


            ///////             Привет & Привет как дела                ///////
            case 'привет':
                if(count($user_message) == 0)
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\Intelligence::hello()));
                else if (count($user_message) > 1 && $user_message[0] == 'как')
                {
                    $whatsupwords = array("дела", "делишки", "жизнь", "поживаешь");
                    if (in_array($user_message[2], $whatsupwords))
                        VKAPI::messageSend(array("user_id" => $data->object->user_id,
                            "message" => message\Intelligence::whatsup()));
                    else
                        VKAPI::messageSend(array("user_id" => $data->object->user_id,
                            "message" => message\Intelligence::hello()));
                }
                else
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\Intelligence::hello()));
                break;




            ///////             Как дела                 ///////
            case 'как':
                if (count($user_message) == 1)
                {
                    $whatsupwords = array("дела", "делишки", "жизнь", "поживаешь");
                    if (in_array($user_message[1], $whatsupwords))
                        VKAPI::messageSend(array("user_id" => $data->object->user_id,
                            "message" => message\Intelligence::whatsup(False)));
                    else
                        VKAPI::messageSend(array("user_id" => $data->object->user_id,
                            "message" => message\OtherRequests::getBasicMessage()));
                }
                else
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\OtherRequests::getBasicMessage()));
                break;




            ///////             Бот                 ///////
            case 'бот':
                if(count($user_message) == 0)
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\Intelligence::bot()));
                else
                    VKAPI::messageSend(array("user_id" => $data->object->user_id,
                        "message" => message\OtherRequests::getBasicMessage()));
                break;





            ///////             Спасибо                 ///////
            case 'спасибо':
                VKAPI::messageSend(array("user_id" => $data->object->user_id,
                    "message" => message\Intelligence::thanks()));


            /////////////////////                      ТЕСТОВЫЕ КОМАНДЫ                    /////////////////////


            ///////             Тест                 ///////
			case "тест":
				//$result = VKAPI::pictureAttachmentMessageSend($data->object->user_id, 'https://www.google.ru/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');
                $reflector = new \ReflectionClass('message_new');
                $result = $reflector->getFileName() . $reflector->getStartLine();

                VKAPI::messageSend(array("user_id" => $data->object->user_id,
				//"message" => file_get_contents("http://kappa.cs.petrsu.ru/~omelchen/vk/bot/lotoftext"),"attachment" => $result));
                "message" => $result));
				break;




            ///////             Фото                 ///////
            case 'фото':
                $result = VKAPI::pictureAttachmentMessageSend($data->object->user_id, 'https://www.google.ru/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png');
                VKAPI::messageSend(array("user_id" => $data->object->user_id, "message" => "смотри че могу", "attachment" => $result));
                break;




            ///////             [Число]                 ///////
            default:
                if (preg_match("/^\d+$/", $user_message[0])) {
                    if (count($user_message) != 2)
						VKAPI::messageSend(array("user_id" => $data->object->user_id,
							"message" => message\Intelligence::check()));
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
        $theme = implode("_", $theme);
        return $theme;
    }
    private static function deleteUnderscore($text)
    {
        $text = str_replace("_", " ", $text);
        return $text;
    }
}
