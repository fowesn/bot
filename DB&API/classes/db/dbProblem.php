<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:25
 */

class dbProblem
{
    public static function getProblem ($user_id)
    {
        $conn = dbConnection::getConnection();

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch() === null)
        {
            /** @throws ?Exception  Specified user_id does not exist */
        }

        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem LEFT JOIN assignment 
                                ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($user_id));
        $row = $stmt->fetch();

        if ($row === null)
        {
            /** @throws ?Exception  No unsolved problems left */
        }

        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        $result = array("problem" => $problem_id);

        return array_merge($result, dbResource::getPreferredResource($user_id, $resource_collection_id));
    }

    public static function getProblemByType ($user_id, $problem_type)
    {
        $conn = dbConnection::getConnection();

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch() === null)
        {
            /** @throws ?Exception  Specified user_id does not exist */
        }

        // Check whether the stated problem_type exists
        $type_check = $conn->prepare('SELECT problem_type_id FROM problem_type WHERE problem_type_code  = ?');
        $type_check->execute(array($problem_type));
        if ($type_check->fetch() === null)
        {
            /** @throws ?Exception  Specified problem_type_code does not exist */
        }

        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem 
                                INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($problem_type, $user_id));
        $row = $stmt->fetch();

        if ($row === null)
        {
            /** @throws ?Exception  No unsolved problems left */
        }

        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        $result = array("problem" => $problem_id);

        return array_merge($result, dbResource::getPreferredResource($user_id, $resource_collection_id));
    }

    public static function getProblemByNumber ($user_id, $exam_item_number)
    {
        $conn = dbConnection::getConnection();

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch() === null)
        {
            /** @throws ?Exception  Specified user_id does not exist */
        }

        // Check whether the stated exam_item_number exists
        $number_check = $conn->prepare('SELECT exam_item_id FROM exam_item WHERE exam_item_number  = ?');
        $number_check->execute(array($exam_item_number));
        if ($number_check->fetch() === null)
        {
            /** @throws ?Exception  Specified user_id does not exist */
        }


        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem 
                                INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($exam_item_number, $user_id));
        $row = $stmt->fetch();

        if ($row === null)
        {
            /** @throws ?Exception  No unsolved problems left */
        }

        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        $result = array("problem" => $problem_id);

        return array_merge($result, dbResource::getPreferredResource($user_id, $resource_collection_id));
    }
}