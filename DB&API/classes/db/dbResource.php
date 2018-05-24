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
        
        $conn = null;
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
                
        $stmt = $conn->prepare('SELECT resource_collection_id FROM resource_collection WHERE resource_collection_id = ?');
        $stmt->execute(array($resource_collection_id));
        if (empty($stmt->fetch()['resource_collection_id']))
        {
          throw new Exception ('Invalid parameter: resource_collection_id ' . ($resource_collection_id === null ? 'NULL' : $resource_collection_id) . ' not found in \'resource_collection\'; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        $stmt = $conn->prepare('SELECT preferred_resource_type FROM user WHERE user_id = ?');
        $stmt->execute(array($user_id));
        $pref = $stmt->fetch()['preferred_resource_type'];
        
        if ($pref === null) 
        {
          throw new Exception ('Invalid parameter: user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        $stmt = $conn->prepare('SELECT resource.resource_name AS name, resource_type.resource_type_code AS type, resource.resource_content AS content 
                                FROM resource
                                INNER JOIN resource_type ON (resource_type.resource_type_id = resource.resource_type_id) 
                                WHERE resource.resource_collection_id = ? AND resource.resource_type_id = ?');
        $stmt->execute(array($resource_collection_id, $pref));
        $resources = array();
        while ($row = $stmt->fetch())
        {
            $resources[] = $row;
        }

        // There can be no resources preferred by user in the collection, remember that!
        if (empty($resources))
        {
            $stmt = $conn->prepare('SELECT resource_type.resource_type_id FROM resource_type 
                                    WHERE NOT resource_type.resource_type_id = ?');
            $stmt->execute(array($pref));
            while ($row = $stmt->fetch()['resource_type_id'])
            {
                 $stmt_ = $conn->prepare('SELECT resource.resource_name AS name, resource_type.resource_type_code AS type, resource.resource_content AS content 
                                FROM resource
                                INNER JOIN resource_type ON (resource_type.resource_type_id = resource.resource_type_id) 
                                WHERE resource.resource_collection_id = ? AND resource.resource_type_id = ?');
                $stmt_->execute(array($resource_collection_id, $row));
                
                while ($row = $stmt_->fetch())
                {
                    $resources[] = $row;
                }
                
                if (empty($resources) === FALSE)
                {
                    return $resources;
                }
            }
        }
             
        $conn = null;
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

        $stmt = $conn->prepare('SELECT user_id FROM user WHERE user_id = ?');
        $stmt->execute(array($user_id));
        if (empty($stmt->fetch()['user_id']))
        {
          throw new Exception ('Invalid parameter: user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        
        if ($resource_type_code === null)
        {
            throw new Exception('Invalid parameter: resource_type_code is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }   
            

        $stmt = $conn->prepare('SELECT resource_type_id FROM resource_type WHERE resource_type_code = ?');
        $stmt->execute(array($resource_type_code));

        // Resources with stated type does not exist
        if (empty($resource_type_id = $stmt->fetch()['resource_type_id']))
        {
            throw new UserExceptions("Такого вида ресурса у меня нет :(
	    Чтобы установить ресурс, которые тебе нравится, напиши \"ресурс [название ресурса]\"", 5);
        }

        $stmt = $conn->prepare('UPDATE user SET preferred_resource_type = ? WHERE user_id = ?');
        $stmt->execute(array($resource_type_id, $user_id));
        
        $conn = null;
        return true;

    }
}
?>