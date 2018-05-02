<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbResource
{
    /**
     * @return array Names of resource types available
     */
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

    /**
     * @param $user_id Global id of a user
     * @param $resource_collection_id Id of the collection that contains statement or solution
     * @return array Contains name, content and type for each resource
     * @throws Exception System error
     */
    public static function getPreferredResource ($user_id, $resource_collection_id)
    {
        $conn = dbConnection::getConnection();

        if ($user_id === null)
        {
            throw new Exception("Invalid parameter: user_id is NULL", 500);
        }

        if ($resource_collection_id === null)
        {
            throw new Exception("Invalid parameter: resource_collection_id is NULL", 500);
        }

        $stmt = $conn->prepare('SELECT preferred_resource_type FROM user WHERE user_id = ?');
        $stmt->execute(array($user_id));
        $pref = $stmt->fetch()['preferred_resource_type'];

        $stmt = $conn->prepare('SELECT resource.resource_name, resource_type.resource_type_code, resource.resource_content 
                                FROM resource, resource_type 
                                WHERE (resource.resource_collection_id = ? 
                                AND resource.resource_type_id = ? 
                                AND resource_type.resource_type_id = resource.resource_type_id)');
        $stmt->execute(array($resource_collection_id, $pref));
        $resources = array();
        while ($row = $stmt->fetch())
        {
            $resources[] = $row;
        }

//        // There can be no resources preferred by user in the collection, remember that!
//        if (empty($row))
//        {
//            /** @throws  ?Exception  Records with specified resource_type_code does not exist */
//        }

        return $resources;
    }

    /**
     * @param $user_id Global id of the user who's setting preferred resource
     * @param $resource_type_code Type of preferred resource
     * @return bool Indicates whether method worked successfully
     * @throws Exception System error
     * @throws UserExceptions Depends on user's actions
     */
    public static function setPreferredResource ($user_id, $resource_type_code)
    {
        $conn = dbConnection::getConnection();

        if ($user_id === null)
        {
            throw new Exception("Invalid parameter: user_id is NULL", 500);
        }

        if ($resource_type_code === null)
        {
            throw new Exception("Invalid parameter: resource_type_code is NULL", 500);
        }

        $stmt = $conn->prepare('SELECT resource_type_id FROM resource_type WHERE resource_type_code = ?');
        $stmt->execute(array($resource_type_code));

        // Resources with stated type does not exist
        if (($resource_type_id = $stmt->fetch()['resource_type_id']) === null)
        {
            throw new UserExceptions("Такого вида ресурса у нас нет :(");
        }

        $stmt = $conn->prepare('UPDATE user SET preferred_resource_type = ? WHERE user_id = ?');
        $stmt->execute(array($resource_type_id, $user_id));
        return true;

    }
}