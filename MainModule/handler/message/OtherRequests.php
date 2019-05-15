<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */

namespace MainModule\handler\message;
class OtherRequests
{
    private static $server_error_message = "Что-то пошло не так. Попробуй снова!";
    //private static $url = 'http://kappa.cs.petrsu.ru/~nestulov/API/v1/public/index.php/';

    /**
     * @return string
     */
    public static function getThemesList() {
        $url = HOST_API . '/problem_types/problem_type';
        //проверка кодов http
        $code = substr(get_headers($url)[0], 9, 3);
        if($code != 200)
            $message = $code . ". " . self::$server_error_message . "\r\n\r\n";
        else {
            $result = json_decode(file_get_contents($url));
            //проверка ошибок пользователя
            if ($result->success !== "true") {
                $message = $result->error->message;
            } else {
                //если ошибок нет
                $message = "У меня есть задания по следующим темам:\r\n\r\n";
                foreach ($result->data as $theme)
                    $message .= $theme . "\r\n";
            }
            $message .= "\r\n\r\nЧтобы получить задание по одной из тем, напиши мне \"задание [название темы]\".";
        }
        return $message;
    }

    /**
     * @return string
     */
    public static function getResourceTypesList() {
        $url = HOST_API . '/resources/resource';
        //проверки кодов http
        $code = substr(get_headers($url)[0], 9, 3);
        if($code != 200)
            $message = $code . ". " . self::$server_error_message . "\r\n\r\n";
        else {
            $result = json_decode(file_get_contents($url));
            //проверка ошибок пользователя
            if ($result->success !== "true") {
                $message = $result->error->message;
            } else {
                //если ошибок нет
                $message = "Вот список ресурсов, в виде которых я могу присылать тебе задания:\r\n\r\n";
                foreach ($result->data as $resource)
                    $message .= $resource . "\r\n";
            }
            $message .= "\r\n\r\nЧтобы установить один из ресурсов, напиши мне \"ресурс [название ресурса]\"";
        }
        return $message;
    }

    /**
     * @param $userID
     * @param $preferredResource
     * @return array
     * @throws \Exception
     */
    public static function setUserPreferredResource($userID, $preferredResource)
    {
        if(!isset($userID))
            throw new \Exception(__FILE__ . " : " . __LINE__ . " Не указан user_id");
        $preferredResource = mb_convert_encoding($preferredResource, 'utf-8', mb_detect_encoding($preferredResource));
        $data = array('resource_type' => urlencode($preferredResource), 'user_id' => (string)$userID, 'service' => 'vk');
        $data_query = http_build_query($data);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, HOST_API . '/resources/resource');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch));
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        //проверка ошибок
        if ($code == 404 || $code == 500)
            $message = $code . ". " . self::$server_error_message;
        else if ($result->success !== "true")
            $message = $result->error->message;

        //если нет ошибок, формирование сбщ пользователю
        else
            $message = "Ресурс \"" . $preferredResource . "\" установлен успешно!";
        return array("user_id" => $userID, "message" => $message);
    }

    /**
     * @param $userID
     * @param $preferredYear
     * @return array
     */
    public static function setUserPreferredYear($userID, $preferredYear)
    {
        $message = $userID . ' ' . $preferredYear;
        return array("user_id" => $userID, "message" => $message);
    }

    public static function getHelpMessage() {
        $message = ["Вот список команд, которые ты можешь использовать:\r\n
                    \"задание\" - я пришлю текст случайного задания из банка заданий\r\n
                    \"задание [название темы]\" - я пришлю текст задания на определенную тематику. Например, \"задание робот\"\r\n
                    \"задание [номер в КИМе]\" - я пришлю текст задания под указанным тобой номером. Например, \"задание 13\"\r\n
                    \"ответ [номер задания]\" - я пришлю правильный ответ на задание под указанным тобой номером. Например, \"ответ 12345\"\r\n
                    \"задания\" - я пришлю тебе список всех нерешённых тобой заданий\r\n
                    \"условие [номер задания]\"- я повторно пришлю тебе условие задания под указанным тобой номером. Например, \"условие 12345\"\r\n",
                    "\"[номер задания] [ответ]\" - я проверю правильность твоего ответа на задание под указанным тобой номером. Например, \"12345 101\"\r\n
                    \"разбор [номер задания]\" - я пришлю текст решения задания под указанным тобой номером. Например, \"разбор 12345\"\r\n
                    \"темы\" - я пришлю список доступных тем, по которым можно запрашивать задания\r\n
                    \"ресурсы\" - я пришлю список типов ресурсов, которые доступны при выдаче задания и условия\r\n",
                    "\"ресурс [название ресурса]\" - я запомню, что задания и разборы ты хочешь получать в указанном виде. Например, \"ресурс изображение\"\r\n
                    \"год [номер года]\" - я запомню, что ты хочешь задания из КИМов с этого года по текущий. Например, \"год 2014\"\r\n
                    \"статистика\" - я пришлю тебе статистику по всем взятым тобой заданиям\r\n
                    \"статистика [номер задания]\" - я пришлю тебе статистику по заданию под указанным тобой номером. Например, \"статистика 12345\""];
        return $message;
    }
    public static function getBasicMessage() {
        $message = "Я не понимаю твой запрос. Напиши \"помощь\", чтобы узнать, что я умею";
        return $message;
    }
}
