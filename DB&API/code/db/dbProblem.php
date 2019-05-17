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
     * @param int $problem_id Идентификатор задания
     * @return integer Идентификатор коллекции ресурсов
     * @throws Exception Внутренняя ошибка
     */
    public static function getStatement(int $problem_id)
    {
        $conn = dbConnection::getConnection();

        $query = $conn->prepare('SELECT problem_statement FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        $statement = $query->fetch()['problem_statement'];

        $conn = null;

        return $statement;
    }



    /** Возвращает идентификатор коллекции ресурсов, содержащей разбор задания,
     ** по идентификатору задания
     * @param int $problem_id Идентификатор задания
     * @return integer Идентификатор коллекции ресурсов
     * @throws Exception Внутренняя ошибка
     */
    public static function getSolution(int $problem_id)
    {
        $conn = dbConnection::getConnection();

        $query = $conn->prepare('SELECT problem_solution FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        $solution = $query->fetch()['problem_solution'];

        $conn = null;

        return $solution;
    }



    /** Возвращает правильный ответ на задание по идентификатору задания
     * @param int $problem_id Идентификатор задания
     * @return string Правильный_ответ
     * @throws Exception Внутренняя ошибка
     */
    public static function getAnswer(int $problem_id)
    {
        $conn = dbConnection::getConnection();

        $query = $conn->prepare('SELECT problem_answer FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        $answer = $query->fetch()['problem_answer'];

        $conn = null;

        return $answer;
    }



    /** Получает доступные темы заданий для запроса заданий по теме
     *
     * @return array Доступные темы заданий
     */
    public static function getProblemTypes()
    {
        $conn = dbConnection::getConnection();

        $problemTypes = array();

        $query = $conn->query('SELECT problem_type_code FROM problem_type');
        while ($row = $query->fetch())
        {
            $problemTypes[] = $row['problem_type_code'];
        }

        $conn = null;

        return $problemTypes;
    }


    /** Получает информацию о задании: год, тему, номер в КИМе
     *
     * @param int $problem_id идентификатор задания
     * @return array Запрошенные данные
     * @throws Exception Внутренняя ошибка
     */
    public static function getProblemData(int $problem_id)
    {
        $data = [];

        $conn = dbConnection::getConnection();

        $query = $conn->prepare('SELECT problem_year FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        $data['year'] = (int)$query->fetch()['problem_year'];

        $query = $conn->prepare('SELECT problem_type.problem_type_code FROM problem_type, problem 
                                 WHERE problem_type.problem_type_id = problem.problem_type_id
                                 AND problem.problem_id = ?');
        $query->execute(array($problem_id));
        $data['problem_type'] = $query->fetch()['problem_type_code'];

        $query = $conn->prepare('SELECT exam_item.exam_item_number FROM exam_item, problem 
                                 WHERE exam_item.exam_item_id = problem.exam_item_id
                                 AND problem.problem_id = ?');
        $query->execute(array($problem_id));
        $data['exam_item_number'] = (int)$query->fetch()['exam_item_number'];

        $conn = null;

        return $data;
    }
}