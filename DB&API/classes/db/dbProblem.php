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
        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem LEFT JOIN assignment 
                                ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($user_id));
        $row = $stmt->fetch();
        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        $result = array("problem" => $problem_id);
        //$result = array_merge($result, dbResource::getPreferredResource($user_id, $resource_collection_id));
        //$result = dbResource::getPreferredResource($user_id, $resource_collection_id);
        return array_merge($result, dbResource::getPreferredResource($user_id, $resource_collection_id));
    }

    public static function getProblemByType ($user_id, $problem_type)
    {
        //mb_internal_encoding("UTF-8");
        $conn = dbConnection::getConnection();

        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem 
                                INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($problem_type, $user_id));
        $row = $stmt->fetch();
        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        return dbResource::getPreferredResource($user_id, $resource_collection_id);
    }

    public static function getProblemByNumber ($user_id, $exam_item_number)
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->prepare('SELECT problem.problem_id, problem.problem_statement FROM problem 
                                INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                WHERE assignment.problem_id IS NULL ORDER BY RAND() LIMIT 1');
        $stmt->execute(array($exam_item_number, $user_id));
        $row = $stmt->fetch();
        $problem_id = $row['problem_id'];
        $resource_collection_id = $row['problem_statement'];
        dbAssignment::assignProblem($user_id, $problem_id);
        return dbResource::getPreferredResource($user_id, $resource_collection_id);
    }
}