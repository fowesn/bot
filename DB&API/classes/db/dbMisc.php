<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 11:04
 */

class dbMisc extends dbConnection
{
    public static function getProblemTypes()
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->query('SELECT problem_type_code FROM problem_type');
        $resources = array();
        while ($row = $stmt->fetch())
        {
            $resources[] = $row['problem_type_code'];
        }
        return $resources;
    }

    public static function getGlobalUserId ($user_id, $platform)
    {
        $conn = dbConnection::getConnection();
        $service = ' user_' . $platform .'_id = ?';
        $query = 'SELECT user_id FROM user WHERE' . $service;
        $stmt = $conn->prepare($query);
        $stmt->execute(array($user_id));
        $global_user = $stmt->fetch();
        return $global_user['user_id'];
    }
}