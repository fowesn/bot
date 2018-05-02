<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 11:04
 */

class dbMisc
{
    /**
     * @return array Contains category names available
     */
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

    /**
     * @param $user_id Id of a user in service used
     * @param $service Source of request that uses API (VK/tg/...)
     * @return mixed Global id of the user
     * @throws Exception System errors
     */
    public static function getGlobalUserId ($user_id, $service)
    {
        $conn = dbConnection::getConnection();
        $columns = array('vk' => 'user_vk_id', 'tg' => 'user_tg_id');

        if ($user_id === null)
        {
            throw new Exception("Invalid parameter: user_id is NULL", 500);
        }

        // Check whether the stated service exists
        if (!isset($columns[$service]))
        {
            throw new Exception("Platform " . $service . " is not supported",404);
        }

        $query = 'SELECT user_id FROM user WHERE ' . $columns[$service] . ' = ?';
        $stmt = $conn->prepare($query);
        $stmt->execute(array($user_id));

        // Register the user, if the stated user_id does not exist
        // Otherwise return "global" user_id
        if (($global_user_id = $stmt->fetch()['user_id']) === null)
        {
            $query = 'INSERT INTO user (user_created, preferred_resource_type, ' . $columns[$service] . ') VALUES (NOW(), 1, ?)';
            $stmt = $conn->prepare($query);
            $stmt->execute(array($user_id));
            // Get an id of last inserted record
            $global_user_id = $conn->lastInsertId();
        }

        return $global_user_id;
    }
}