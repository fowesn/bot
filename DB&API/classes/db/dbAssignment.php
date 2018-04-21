<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbAssignment
{
    // для прототипа работает :)
    public static function assignProblem ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->prepare('INSERT INTO  assignment (problem_id, user_id, assignment_last_answer, assigned, correct_answer_provided) VALUES (?, ?, NULL, NOW(), FALSE )');
        $stmt->execute(array($problem_id, $user_id));
        return true;
    }

    public static function assignAnswer ($user_id, $problem_id, $answer)
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $stmt->execute(array($problem_id, $user_id));
        $assignment_id = $stmt->fetch()['assignment_id'];

        $stmt = $conn->prepare('INSERT INTO answer (assignment_id, solution_answer, solution_provided) VALUES (?, ?, NOW())');
        $stmt->execute(array($assignment_id, $answer));
        return true;
    }
}