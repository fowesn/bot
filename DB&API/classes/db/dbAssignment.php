<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbAssignment extends dbConnection
{
    public static function assignProblem ($user_id, $problem_id)
    {
        //$user = dbMisc::getGlobalUserId($user_id)
        $conn = dbConnection::getConnection();
        $stmt = $conn->prepare('INSERT INTO  assignment (problem_id, user_id, assignment_last_answer, assigned, correct_answer_provided) VALUES (?, ?, NULL, ?, FALSE )');
        $stmt->execute(array($problem_id, $user_id, time()));
    }

    public static function assignAnswer ($user_id, $problem_id, $answer)
    {

    }
}