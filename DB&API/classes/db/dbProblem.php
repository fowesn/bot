<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:25
 */

class dbProblem
{
    /**
     * @param $user_id Global id of the user who requested problem
     * @return array Contains id of the assigned problem and statement stored in resources
     * @throws Exception System errors
     * @throws UserExceptions Depend on user's actions
     */
    public static function getProblem ($user_id)
    {
        $conn = dbConnection::getConnection();

        if ($user_id === null)
        {
            throw new Exception("Invalid parameter: user_id is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 500);
        }

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch()['user_id'] === null)
        {
            throw new Exception("Invalid parameter: user_id not found in user; Method: " . __METHOD__ . "; line: " . __LINE__, 500);
        }

        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem LEFT JOIN assignment 
                                ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($user_id));
        $row = $stmt->fetch();

        // No unsolved problems left
        if (empty($row))
        {
            throw new UserExceptions("Вы гений или просто сын/дочь маминой подруги - у нас кончились задания :(");
        }

        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        $result = array("problem" => $problem_id);

        return array_merge($result, dbResource::getPreferredResource($user_id, $resource_collection_id));
    }

    /**
     * @param $user_id Global id of the user who requested problem
     * @param $problem_type_code Category of requested problem
     * @return array Contains id of the assigned problem and statement stored in resources
     * @throws Exception System errors
     * @throws UserExceptions Depend on user's actions
     */
    public static function getProblemByType ($user_id, $problem_type_code)
    {
        $conn = dbConnection::getConnection();

        if ($user_id === null)
        {
            throw new Exception("Invalid parameter: user_id is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 500);
        }

        if ($problem_type_code === null)
        {
            throw new Exception("Invalid parameter: problem_type is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 500);
        }

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch()['user_id'] === null)
        {
            throw new Exception("Invalid parameter: user_id not found in user; Method: " . __METHOD__ . "; line: " . __LINE__, 500);
        }

        // Check whether the stated problem_type exists
        $type_check = $conn->prepare('SELECT problem_type_id FROM problem_type WHERE problem_type_code  = ?');
        $type_check->execute(array($problem_type_code));
        if ($type_check->fetch()['problem_type_id'] === null)
        {
            throw new UserExceptions("Такой категории мы не знаем!");
        }

        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem 
                                INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($problem_type_code, $user_id));
        $row = $stmt->fetch();

        // No unsolved problems left
        if (empty($row))
        {
            throw new UserExceptions("Вы гений или просто сын/дочь маминой подруги - у нас кончились задания :(");
        }

        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        $result = array("problem" => $problem_id);

        return array_merge($result, dbResource::getPreferredResource($user_id, $resource_collection_id));
    }

    /**
     * @param $user_id Global id of the user who requested problem
     * @param $exam_item_number Exam number of requested problem
     * @return array Contains id of the assigned problem and statement stored in resources
     * @throws Exception System errors
     * @throws UserExceptions Depend on user's actions
     */
    public static function getProblemByNumber ($user_id, $exam_item_number)
    {
        $conn = dbConnection::getConnection();

        if ($user_id === null)
        {
            throw new Exception("Invalid parameter: user_id is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 500);
        }

        if ($exam_item_number === null)
        {
            throw new Exception("Invalid parameter: exam_item_number is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 500);
        }

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch()['user_id'] === null)
        {
            throw new Exception("Invalid parameter: user_id not found in user; Method: " . __METHOD__ . "; line: " . __LINE__, 500);
        }

        // Check whether the stated exam_item_number exists
        $number_check = $conn->prepare('SELECT exam_item_id FROM exam_item WHERE exam_item_number  = ?');
        $number_check->execute(array((int)$exam_item_number));
        if ($number_check->fetch()['exam_item_id'] === null)
        {
            throw new UserExceptions("Такого номера задания нет в экзамене!");
        }


        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem 
                                INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($exam_item_number, $user_id));
        $row = $stmt->fetch();

        // No unsolved problems left
        if (empty($row))
        {
            throw new UserExceptions("Вы гений или просто сын/дочь маминой подруги - у нас кончились задания :(");
        }

        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        $result = array("problem" => $problem_id);

        return array_merge($result, dbResource::getPreferredResource($user_id, $resource_collection_id));
    }
}