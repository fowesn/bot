<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:25
 */

class dbProblem
{
    /** Возвращает идентификатор коллекции ресурсов, содержащей условие задания,
     ** по идентификатору задания
     * @param $problem_id integer Идентификатор задания
     * @return integer Идентификатор_коллекции_ресурсов
     * @throws Exception Внутренняя_ошибка
     */
    public static function getStatement ($problem_id)
    {
        $conn = dbConnection::getConnection();

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query = $conn->bindParam('1', $problem_id, PDO::PARAM_INT);
        $query->execute();
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }
        else
        {
            $query = $conn->prepare('SELECT problem_statement FROM problem
                                     WHERE problem_id = ?');
            $query->execute(array($problem_id));
        }

        unset($conn);

        return $query->fetch()['problem_statement'];
    }



    /** Возвращает идентификатор коллекции ресурсов, содержащей разбор задания,
     ** по идентификатору задания
     * @param $problem_id integer Идентификатор задания
     * @return integer Идентификатор_коллекции_ресурсов
     * @throws Exception Внутренняя ошибка
     */
    public static function getSolution ($problem_id)
    {
        $conn = dbConnection::getConnection();

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query = $conn->bindParam('1', $problem_id, PDO::PARAM_INT);
        $query->execute();
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }
        else
        {
            $query = $conn->prepare('SELECT problem_solution FROM problem
                                     WHERE problem_id = ?');
            $query->execute(array($problem_id));
        }

        unset($conn);

        return $query->fetch()['problem_solution'];
    }



    /** Возвращает правильный ответ на задание по идентификатору задания
     * @param $problem_id integer Идентификатор задания
     * @return string Правильный_ответ
     * @throws Exception Внутренняя_ошибка
     */
    public static function getAnswer ($problem_id)
    {
        $conn = dbConnection::getConnection();

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query = $conn->bindParam('1', $problem_id, PDO::PARAM_INT);
        $query->execute();
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }
        else
        {
            $query = $conn->prepare('SELECT problem_answer FROM problem
                                     WHERE problem_id = ?');
            $query->execute(array($problem_id));
        }

        unset($conn);

        return $query->fetch()['problem_answer'];
    }



    /** Получает доступные темы заданий для запроса заданий по теме
     *
     * @return array Доступные темы заданий
     */
    public static function getProblemTypes()
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->query('SELECT problem_type_code FROM problem_type');
        $problemTypes = array();
        while ($row = $stmt->fetch())
        {
            $problemTypes[] = $row['problem_type_code'];
        }

        unset($conn);

        return $problemTypes;
    }
}
?>