<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbResource
{
    public static function getResourceTypes()
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->query('SELECT resource_type_code FROM resource_type');
        $resources = array();
        while ($row = $stmt->fetch())
        {
            $resources[] = $row['resource_type_code'];
        }
        return $resources;
    }

    public static function getPreferredResource ($user_id, $resource_collection_id)
    {
        $conn = dbConnection::getConnection();

        // Check whether the stated user exists
        $stmt = $conn->prepare('SELECT preferred_resource_type FROM user WHERE user_id = ?');
        $stmt->execute(array($user_id));
        if (($pref = $stmt->fetch()['preferred_resource_type']) === null)
        {
            /** @throws  ?Exception  Record with specified user_id does not exist */
        }

        $stmt = $conn->prepare('SELECT resource.resource_name, resource_type.resource_type_code, resource.resource_content FROM resource, resource_type WHERE (resource.resource_collection_id = ? AND resource.resource_type_id = ? AND resource_type.resource_type_id = resource.resource_type_id)');
        $stmt->execute(array($resource_collection_id, $pref));
        $resources = array();
        while ($row = $stmt->fetch())
        {
            $resources[] = $row;
        }

        // is that right?
        if (empty($row))
        {
            /** @throws  ?Exception  Records with specified resource_type_code does not exist */
        }

        return $resources;
    }

    public static function setPreferredResource ($user_id, $resource_type_code)
    {
        $conn = dbConnection::getConnection();

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch() === null)
        {
            /** @throws ?Exception  Specified user_id does not exist */
        }

        $stmt = $conn->prepare('SELECT resource_type_id FROM resource_type WHERE resource_type_code = ?');
        $stmt->execute(array($resource_type_code));

        if (($resource_type_id = $stmt->fetch()['resource_type_id']) === null)
        {
            /** @throws  ?Exception  Record with specified resource_type_code does not exist */
        }

        $stmt = $conn->prepare('UPDATE user SET preferred_resource_type = ? WHERE user_id = ?');
        $stmt->execute(array($resource_type_id, $user_id));
        return true;

    }
}