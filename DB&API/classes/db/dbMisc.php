<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 11:04
 */

class dbMisc
{
    // для прототипа работает :)
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

    // для прототипа работает :)
    public static function getGlobalUserId ($user_id, $platform)
    {
        $conn = dbConnection::getConnection();
        $columns = array('vk' => 'user_vk_id');
        //$service = ' user_' . $platform .'_id = ?';
        $query = 'SELECT user_id FROM user WHERE ' . $columns[$platform] . ' = ?';
        $stmt = $conn->prepare($query);
        $stmt->execute(array($user_id));

        if (($global_user_id = $stmt->fetch()['user_id']) === null)
        {
            $query = 'INSERT INTO user (user_created, preferred_resource_type, ' . $columns[$platform] . ') VALUES (NOW(), 1, ?)';
            $stmt = $conn->prepare($query);
            $stmt->execute(array($user_id));
            $global_user_id = $stmt->fetch()['user_id'];
        }

        return $global_user_id;
    }

    // уже не понадобится
    // для прототипа работает :)
    public static function registerUser ($user_id, $platform)
    {
        $conn = dbConnection::getConnection();

        $columns = array('vk' => 'user_vk_id');

        $query = 'SELECT user_id FROM user WHERE ' . $columns[$platform] . ' = ?';
        $stmt = $conn->prepare($query);
        $stmt->execute(array($user_id));
        if ($stmt->fetch()['user_id'] === null)
        {
            $query = 'INSERT INTO user (user_created, preferred_resource_type, ' . $columns[$platform] . ') VALUES (NOW(), 1, ?)';
            $stmt = $conn->prepare($query);
            $stmt->execute(array($user_id));
        }

        return true;
    }
}