<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbAssignment
{
    /**
     * @param $user_id Id of a user to whom a problem is assigned
     * @param $problem_id Id of a problem assigned
     * @return bool Indicates whether the method worked successful
     * @throws Exception System errors
     */
    public static function assignProblem ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        // Check whether the stated user exists
        // We need this one because of foreign key constraints and using INSERT
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch()['user_id'] === null)
        {
            throw new Exception('Invalid parameter: user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        // Check whether the stated problem exists
        // We need this one because of foreign key constraints and using INSERT
        $problem_check = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $problem_check->execute(array($problem_id));
        if ($problem_check->fetch()['problem_id'] === null)
        {
            throw new Exception('Invalid parameter: problem_id ' . ($problem_id === NULL ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }
        
        $stmt = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $stmt->execute(array($problem_id, $user_id));
        if (!empty($stmt->fetch()['assignment_id']))
        {
            throw new Exception('Problem_id ' . $problem_id . ' has already been assigned to user ' . $user_id . '; record id = '. $stmt->fetch()['assignment_id'], 500);
        }

        $stmt = $conn->prepare('INSERT INTO  assignment (problem_id, user_id, assignment_last_answer, assigned, correct_answer_provided) VALUES (?, ?, NULL, NOW(), FALSE )');
        $stmt->execute(array($problem_id, $user_id));
	
        return true;
    }

    /**
     * @param $user_id Id of a user whose answer is saved
     * @param $problem_id Id of an assigned problem
     * @param $answer User's answer to save
     * @return mixed Id of updated assignment
     * @throws Exception System errors
     * @throws UserExceptions Depend on user's actions
     */
    public static function assignAnswer ($user_id, $problem_id, $answer)
    {
        $conn = dbConnection::getConnection();

        if ($user_id === null || !is_numeric($user_id))
        {
            throw new Exception('Invalid parameter: user_id ' . ($user_id === null ? 'NULL' : $user_id) . 'not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        if ($problem_id === null || !is_numeric($problem_id))
        {
            throw new Exception('Invalid parameter: problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . 'not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        if ($answer === null)
        {
            throw new Exception('Invalid parameter: answer is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        // User can't give an answer to the task he wasn't assigned to
        $stmt = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $stmt->execute(array($problem_id, $user_id));
        if (($assignment_id = $stmt->fetch()['assignment_id']) === null)
        {
            throw new UserExceptions('Вы не получали задания, на которое пытаетесь дать ответ!', 4);
        }

        // Saving last answer in assignment
        $stmt = $conn->prepare('UPDATE assignment SET assignment_last_answer = ? WHERE assignment_id = ?');
        $stmt->execute(array($answer, $assignment_id));

        $stmt = $conn->prepare('INSERT INTO answer (assignment_id, solution_answer, solution_provided) VALUES (?, ?, NOW())');
        $stmt->execute(array($assignment_id, $answer));

        return $assignment_id;
    }
}
?>