<?php
/**
 * Created by PhpStorm.
 * User: Fow
 * Date: 13.04.2018
 * Time: 14:39
 */


class OtherRequests
{
    private static $server_error_message = "Что-то пошло не так. Попробуй снова!";
    private static $url = 'http://kappa.cs.petrsu.ru/~nestulov/API/public/index.php/';
    public static function getThemesList() {
        $url = self::$url . 'problem_types/problem_type';
        //проверка кодов http
        $code = substr(get_headers($url)[0], 9, 3);
        if($code != 200)
            $message = $code . " " . self::$server_error_message . "\r\n\r\n";
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
            $message .= "\r\n\r\nЧтобы получить задание по одной из тем, напиши мне \"задание <название темы>\".";
        }
        return $message;
    }
    public static function getResourceTypesList() {
        $url = self::$url . 'resources/resource';
        //проверки кодов http
        $code = substr(get_headers($url)[0], 9, 3);
        if($code != 200)
            $message = $code . " " . self::$server_error_message . "\r\n\r\n";
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
            $message .= "\r\n\r\nЧтобы установить один из ресурсов, напиши мне \"ресурс <название ресурса>\"";
        }
        return $message;
    }
    public static function setUserPreferredResource($userId, $preferredResource)
    {
        $preferredResource = mb_convert_encoding($preferredResource, 'utf-8', mb_detect_encoding($preferredResource));
        $data = array('resource_type' => urlencode($preferredResource), 'user_id' => (string)$userId, 'service' => 'vk');
        $data_query = http_build_query($data);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://kappa.cs.petrsu.ru/~nestulov/API/public/index.php/resources/resource');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch));
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);


        //$response = json_decode(file_get_contents('http://kappa.cs.petrsu.ru/~nestulov/API/public/index.php/resources/resource?' . $data));

        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://kappa.cs.petrsu.ru/~nestulov/API/public/index.php/resources/resource');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result= json_decode(curl_exec($ch));
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);*/


        //проверка ошибок
        if ($code == 404 || $code == 500)
            $message = $code . ". Что-то пошло не так. Попробуй ещё раз!";
        else if ($result->success !== "true")
            $message = $result->error->message;

        //если нет ошибок, формирование сбщ пользователю
        else
            $message = "Ресурс \"" . $preferredResource . "\" установлен успешно!";
        return array("user_id" => $userId, "message" => $message);
    }
    public static function getHelpMessage() {
        $message = ["Вот список команд, которые ты можешь использовать:\r\n
                    \"задание\" - я пришлю текст случайного задания из банка заданий\r\n
                    \"задание <название темы>\" - я пришлю текст задания на определенную тематику. Например, \"задание робот\"\r\n
                    \"задание <номер в КИМе>\" - я пришлю текст задания под указанным тобой номером. Например, \"задание 13\"\r\n
                    \"ответ <номер задания>\" - я пришлю правильный ответ на задание под указанным тобой номером. Например, \"ответ 12345\"",
                    "\"<номер задания> <ответ>\" - я проверю правильность твоего ответа на задание под указанным тобой номером. Например, \"12345 101\"\r\n
                    \"разбор <номер задания>\" - я пришлю текст решения задания под указанным тобой номером. Например, \"разбор 12345\"\r\n
                    \"темы\" - я пришлю список доступных тем, по которым можно запрашивать задания\r\n
                    \"ресурсы\" - я пришлю список типов ресурсов, которые доступны при выдаче задания и условия\r\n
                    \"ресурс <название ресурса>\" - я запомню, что задания и разборы ты хочешь получать в указанном виде (конечно, если я так умею). Например, \"ресурс изображения\""];
        return $message;
    }
    public static function getBasicMessage() {
        $message = "Я не понимаю, что ты от меня хочешь. Напиши \"помощь\", чтобы узнать, что я умею";
        return $message;
    }
}
