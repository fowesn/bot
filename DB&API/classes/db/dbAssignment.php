<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbAssignment
{
    public static function assignProblem ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch() === null)
        {
            /** @throws ?Exception  Specified user_id does not exist */
        }

        // Check whether the stated problem exists
        $problem_check = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $problem_check->execute(array($problem_id));
        if ($problem_check->fetch() === null)
        {
            /** @throws ?Exception  Specified problem_id does not exist */
        }

        $stmt = $conn->prepare('INSERT INTO  assignment (problem_id, user_id, assignment_last_answer, assigned, correct_answer_provided) VALUES (?, ?, NULL, NOW(), FALSE )');
        $stmt->execute(array($problem_id, $user_id));
        return true;
    }

    public static function assignAnswer ($user_id, $problem_id, $answer)
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $stmt->execute(array($problem_id, $user_id));
        if (($assignment_id = $stmt->fetch()['assignment_id']) === null)
        {
            /** @throws ?Exception  Requested assignment_id does not exist */
        }

        $stmt = $conn->prepare('INSERT INTO answer (assignment_id, solution_answer, solution_provided) VALUES (?, ?, NOW())');
        $stmt->execute(array($assignment_id, $answer));
        return true;
    }
}