<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */

class OtherRequests
{
    public static function getThemesList($userId) {
        $message ="";
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getResourceTypesList($userId) {
        $message ="";
        return array("user_id" => $userId, "message" => $message);
    }
    public static function setUserPreferredResource($userId, $preferredResource) {
        $message ="";
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getHelpMessage($userId) {
        $message = "Вот список команд, которые ты можешь использовать:
                    \"случайное задание\" - я пришлю текст случайного задания из банка заданий
                    \"задание <название темы>\" - я пришлю текст задания на определенную тематику. Например,\"задание робот\"
                    \"задание <номер в КИМе>\" - я пришлю текст задания под указанным тобой номером. Например, \"задание 13\"
                    \"ответ <номер задания>\" - я пришлю правильный ответ на задание под указанным тобой номером. Например, \"ответ 12345\"
                    \"<номер задания> <ответ>\" - я проверю правильность твоего ответа на задание под указанным тобой номером. Например, \"12345 101\"
                    \"разбор <номер задания>\" - я пришлю текст решения задания под указанным тобой номером. Например, \"разбор 12345\"
                    \"ресурсы\" - я пришлю тебе список доступных ресурсов
                    \"ресурс <название ресурса>\" - я запомню тот ресурс, который тебе нравится (помни, что не все задания доступны в одном ресурсе; если задания недоступны в том виде, который тебе нравится, я пришлю тебе в виде текста или изображения, если задание не может быть отображено только текстом)";
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getBasicMessage($userId) {
        $message = "Я не понимаю, что ты от меня хочешь. Напиши \"помощь\", чтобы узнать, что я умею";
        return array("user_id" => $userId, "message" => $message);
    }
}