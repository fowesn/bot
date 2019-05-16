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
    public static function getGlobalUserId($user, $service)
    {
        $conn = dbConnection::getConnection();

        $query = $conn->prepare('SELECT user_id FROM user WHERE ' . SERVICES[$service]. ' = ?');
        $query->execute(array($user));

        // Если указанный user_id не существует, то регистрируем пользователя
        // Иначе, возвращаем глобальный user_id
        if (($global_user_id = $query->fetch()['user_id']) === null)
        {
            $query = 'INSERT INTO user (user_created, preferred_resource_type, ' . SERVICES[$service] . ', year_range) 
                      VALUES (NOW(), 1, ?, (SELECT MAX(problem_year) FROM problem))';
            $query = $conn->prepare($query);
            $query->execute(array($user));
            // получение первичного ключа последней добавленной записи
            $global_user_id = $conn->lastInsertId();
        }

        $conn = null;

        return $global_user_id;
    }



    /**
     * @param $user_id
     * @param $year
     * @return mixed
     */
    public static function setYearRange($user_id, $year)
    {
        $conn = dbConnection::getConnection();

        $query = $conn->prepare('UPDATE user SET year_range = ? WHERE user_id = ?');
        $query->execute(array($year, $user_id));

        $query = $conn->prepare('SELECT year_range FROM user 
                                 WHERE user_id = ?');
        $query->execute(array($user_id));
        $answer = $query->fetch()['year_range'];

        $conn = null;

        return $answer;
    }
}