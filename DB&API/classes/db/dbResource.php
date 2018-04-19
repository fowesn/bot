<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbResource extends dbConnection
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

    }

    public static function setPreferredResource ($user_id, $resource_type_name)
    {

    }
}