<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 11:04
 */

class dbMisc
{

    /** Возвращает глобальный идентификатор пользователя по имени сервиса и идентификатору пользователя в указанном сервисе
     *
     * @param $user integer Идентификатор пользователя в указанном сервисе
     * @param $service string Используемый сервис
     * @return integer Глобальный идентификатор пользователя
     * @throws Exception Внутренняя ошибка
     */
    public static function getGlobalUserId ($user, $service)
    {
        $conn = dbConnection::getConnection();

        // проверка существования сервиса
        if (SERVICES[$service] === null)
        {
            throw new Exception('Service ' . $service . ' is not supported; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        $query = $conn->prepare('SELECT user_id FROM user WHERE ? = ?');
        $query->bindParam('1', SERVICES[$service], PDO::PARAM_STR);
        $query->bindParam('2', $user, PDO::PARAM_INT);
        $query->execute();

        // Если указанный user_id не существует, то регистрируем пользователя
        // Иначе, возвращаем глобальный user_id
        if (($global_user_id = $query->fetch()['user_id']) === null)
        {
            $query = 'INSERT INTO user (user_created, preferred_resource_type, ' . SERVICES[$service] . ') VALUES (NOW(), 1, ?)';
            $query = $conn->prepare($query);
            $query->execute(array($user));
            // получение первичного ключа последней добавленной записи
            $global_user_id = $conn->lastInsertId();
        }

        return $global_user_id;
    }
}
?>