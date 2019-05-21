<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbResource
{
    /** Получает доступные типы ресурсов для установления предпочитаемого типа ресурса
     *
     * @return array Названия доступных типов ресурсов
     */
    public static function getResourceTypes()
    {
        $conn = dbConnection::getConnection();

        $resources = array();

        $query = $conn->query('SELECT resource_type_code FROM resource_type');
        while ($row = $query->fetch())
        {
            $resources[] = $row['resource_type_code'];
        }

        $conn = null;

        return $resources;
    }



    /** Для заданного пользователя устанавливает предпочитаемый тип ресурса
     *
     * @param int  Глобальный идентификатор пользователя
     * @param string $resource_type_code Предпочитаемый тип ресурса
     * @return mixed Установленный тип ресурса
     * @throws APIException Указанный тип ресурса не существует
     * @throws Exception Внутренняя ошибка
     */
    public static function setPreferredResource(int $user_id, string $resource_type_code)
    {
        $conn = dbConnection::getConnection();

        $query = $conn->prepare('SELECT resource_type_id FROM resource_type WHERE resource_type_code = ?');
        $query->execute(array($resource_type_code));

        // Указанного типа ресурса не существует
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if (($resource_type_id = $query->fetch()['resource_type_id']) === null)
        {
            throw new APIException(RESOURCE_TYPE_NOT_FOUND, RESOURCE_TYPE_NOT_FOUND_MSG, 422);
        }

        $query = $conn->prepare('UPDATE user SET preferred_resource_type = ? WHERE user_id = ?');
        $query->execute(array($resource_type_id, $user_id));

        $query = $conn->prepare('SELECT resource_type.resource_type_code FROM resource_type, user 
                                 WHERE user.preferred_resource_type = resource_type.resource_type_id
                                 AND user.user_id = ?');
        $query->execute(array($user_id));
        $answer = $query->fetch()['resource_type_code'];

        $conn = null;

        return $answer;
    }



    /** Для заданного пользователя получает предопчитаемый тип ресурса
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param $resource_collection_id integer Идентификатор коллекции
     * @return array Массив с именем, и содержимым ресурса предпочитаемого типа
     * @throws Exception Внутренняя ошибка
     */
    public static function getPreferredResource(int $user_id, int $resource_collection_id)
    {
        $conn = dbConnection::getConnection();

        // проверка существования коллекции
        $query = $conn->prepare('SELECT resource_collection_id FROM resource_collection WHERE resource_collection_id = ?');
        $query->execute(array($resource_collection_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if (($query->fetch()['resource_collection_id']) === null) {
            throw new Exception ('resource_collection_id ' . ($resource_collection_id === null ? 'NULL' : $resource_collection_id) . ' not found in \'resource_collection\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // получение предпочитаемого типа ресурса
        $query = $conn->prepare('SELECT preferred_resource_type FROM user WHERE user_id = ?');
        $query->execute(array($user_id));

        if (($preferred_resource_type = $query->fetch()['preferred_resource_type']) === null) {
            throw new Exception ('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // получаем ресурсы предпочитаемого типа из указанной коллекции
        $query = $conn->prepare('SELECT resource.resource_name AS name, 
                                        resource_type.resource_type_code AS type, 
                                        resource.resource_content AS content 
                                FROM resource
                                INNER JOIN resource_type ON (resource_type.resource_type_id = resource.resource_type_id) 
                                WHERE resource.resource_collection_id = ? AND resource.resource_type_id = ?');
        $query->execute(array($resource_collection_id, $preferred_resource_type));
        while ($row = $query->fetch()) {
            $resources[] = $row;
        }

        // если массив resources оказался пустой, значит в колллекции не нашлось предпочитаемого типа ресурса
        if (empty($resources)) {
            // получаем типы ресурсов, кроме предпочитаемого
            $query = $conn->prepare('SELECT resource_type.resource_type_id FROM resource_type 
                                    WHERE NOT resource_type.resource_type_id = ?');
            $query->execute(array($preferred_resource_type));

            // для полученных типов ресурсов пытаемся извлечь ресурсы из коллекции
            while ($row = $query->fetch()['resource_type_id']) {
                $query = $conn->prepare('SELECT resource.resource_name AS name, 
                                                resource_type.resource_type_code AS type, 
                                                resource.resource_content AS content 
                                         FROM resource
                                         INNER JOIN resource_type ON (resource_type.resource_type_id = resource.resource_type_id) 
                                         WHERE resource.resource_collection_id = ? AND resource.resource_type_id = ?');
                $query->execute(array($resource_collection_id, $row));

                while ($row = $query->fetch()) {
                    $resources[] = $row;
                }

                // если для указанного типа ресурса нашлись ресурсы, возвращаем их
                if (empty($resources) === FALSE) {
                    return $resources;
                }
            }
        }

        $conn = null;

        return $resources;
    }
}