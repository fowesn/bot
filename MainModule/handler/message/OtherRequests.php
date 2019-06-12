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
        $url = HOST_API . '/resources';
        //проверки кодов http
        $code = substr(get_headers($url)[0], 9, 3);
        if($code == 200)
        {
            $result = json_decode(file_get_contents($url));
            $message = "Вот список ресурсов, в виде которых я могу присылать тебе задания:\r\n\r\n";
            foreach ($result->data as $resource)
                $message .= $resource . "\r\n";
            $message .= "\r\n\r\nЧтобы установить один из ресурсов, напиши мне \"ресурс [название ресурса]\"";
        }
        else
            $message = $code . ". " . self::$server_error_message . "\r\n\r\n";

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
        $data = array('resource_type' => $preferredResource, 'service' => 'vk');
        $data_query = http_build_query($data);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, HOST_API . '/users/' . $userID . '/resource');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = json_decode(curl_exec($ch));
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($code == 200)
            $message = "Ресурс \"" . $preferredResource . "\" установлен успешно!";
        elseif ($code == 422)
            $message = "Такого ресурса у меня нет. Чтобы посмотреть список ресурсов, напиши мне \"ресурсы\"";
        else
            $message = $code . ". " . self::$server_error_message;
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

}
