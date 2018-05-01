<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 11:04
 */

class dbMisc
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
        $columns = array('vk' => 'user_vk_id');

        // Check whether the stated service exists
        if (!isset($columns[$platform]))
        {
            /** @throws  ?Exception  Column that represents the service does not exist */
        }

        $query = 'SELECT user_id FROM user WHERE ' . $columns[$platform] . ' = ?';
        $stmt = $conn->prepare($query);
        $stmt->execute(array($user_id));

        // Register the user, if the stated user_id does not exist
        // Otherwise return "global" user_id
        if (($global_user_id = $stmt->fetch()['user_id']) === null)
        {
            $query = 'INSERT INTO user (user_created, preferred_resource_type, ' . $columns[$platform] . ') VALUES (NOW(), 1, ?)';
            $stmt = $conn->prepare($query);
            $stmt->execute(array($user_id));
            // Get an id of last inserted record
            $global_user_id = $stmt->fetch()['user_id'];
        }

        return $global_user_id;
    }
}