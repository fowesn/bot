<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbResource
{
    /** Получает доступные типы ресурсов для установления предпочитаемого ресурса
     *
     * @return array Названия доступных типов ресурсов
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

        // проверка существования коллекции
        $query = $conn->prepare('SELECT resource_collection_id FROM resource_collection WHERE resource_collection_id = ?');
        $query->execute(array($resource_collection_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if (($query->fetch()['resource_collection_id']) === null)
        {
          throw new Exception ('resource_collection_id ' . ($resource_collection_id === null ? 'NULL' : $resource_collection_id) . ' not found in \'resource_collection\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // проверка существования пользователя и получение предпочитаемого типа ресурса
        $query = $conn->prepare('SELECT preferred_resource_type FROM user WHERE user_id = ?');
        $query->execute(array($user_id));
        
        if (($preferred_resource_type = $query->fetch()['preferred_resource_type']) === null)
        {
          throw new Exception ('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // получаем ресурсы предпочитаемого типа из указанной коллекции
        $query = $conn->prepare('SELECT resource.resource_name AS resource_name, resource_type.resource_type_code AS resource_type, resource.resource_content AS resource_content 
                                FROM resource
                                INNER JOIN resource_type ON (resource_type.resource_type_id = resource.resource_type_id) 
                                WHERE resource.resource_collection_id = ? AND resource.resource_type_id = ?');
        $query->execute(array($resource_collection_id, $preferred_resource_type));
        $resources = array();
        while ($row = $query->fetch())
        {
            $resources[] = $row;
        }

        // если массив resources оказался пустой, значит в колллекции не нашлось предпочитаемого типа ресурса
        if (empty($resources))
        {
            // получаем типы ресурсов, кроме предпочитаемого
            $query = $conn->prepare('SELECT resource_type.resource_type_id FROM resource_type 
                                    WHERE NOT resource_type.resource_type_id = ?');
            $query->execute(array($preferred_resource_type));

            // для полученных типов ресурсов пытаемся извлечь ресурсы из коллекции
            while ($row = $query->fetch()['resource_type_id'])
            {
                $query = $conn->prepare('SELECT resource.resource_name AS resource_name, resource_type.resource_type_code AS resource_type, resource.resource_content AS resource_content 
                                FROM resource
                                INNER JOIN resource_type ON (resource_type.resource_type_id = resource.resource_type_id) 
                                WHERE resource.resource_collection_id = ? AND resource.resource_type_id = ?');
                $query->execute(array($resource_collection_id, $row));

                while ($row = $query->fetch())
                {
                    $resources[] = $row;
                }

                // если для указанного типа ресурса нашлись ресурсы, возвращаем их
                if (empty($resources) === FALSE)
                {
                    return $resources;
                }
            }
        }
             
        unset ($conn);

        return $resources;
    }


    /**
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param $resource_type_code string Тип предпочитаемого ресурса
     * @throws UserException Указанный тип ресурса не найден
     * @throws Exception Внутренняя ошибка
     */
    public static function setPreferredResource ($user_id, $resource_type_code)
    {
        $conn = dbConnection::getConnection();

        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if (($query->fetch()['user_id']) === null)
        {
          throw new Exception ('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        $query = $conn->prepare('SELECT resource_type_id FROM resource_type WHERE resource_type_code = ?');
        $query->execute(array($resource_type_code));

        // Указанного типа ресурса не существует
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if (($resource_type_id = $query->fetch()['resource_type_id']) === null)
        {
            throw new UserException(RESOURCE_TYPE_NOT_FOUND, RESOURCE_TYPE_NOT_FOUND_MSG, 200);
        }

        $query = $conn->prepare('UPDATE user SET preferred_resource_type = ? WHERE user_id = ?');
        $query->execute(array($resource_type_id, $user_id));
        
        unset ($conn);
    }
}
?>