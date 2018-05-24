<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 02.05.2018
 * Time: 13:21
 */

class checkAnswer
{
    /**
     * @param $assignment_id Id of an assignment, that may change
     * @param $answer User's answer to check
     * @return bool Indicates whether user's answer is correct
     * @throws Exception System Error
     */
    public static function checkB ($assignment_id, $answer)
    {
        $conn = dbConnection::getConnection();

        if ($answer === null)
        {
            throw new Exception('Invalid parameter: answer is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        $stmt = $conn->prepare('SELECT problem_id FROM assignment WHERE assignment_id = ?');
        $stmt->execute(array($assignment_id));
        $problem_id = $stmt->fetch()['problem_id'];
        
        if ($problem_id === null)
        {
            throw new Exception('Invalid parameter: assignment_id ' . $assignment_id . ' not found in \'assignment\'; Method: ' . __METHOD__  . '; line: ' . __LINE__, 500);
        }

        $stmt = $conn->prepare('SELECT problem_answer FROM problem WHERE problem_id = ?');
        $stmt->execute(array($problem_id));

        if ($stmt->fetch()['problem_answer'] === $answer)
        {
            $stmt = $conn->prepare('UPDATE assignment SET correct_answer_provided = 1 WHERE assignment_id = ?');
            $stmt->execute(array($assignment_id));
            
            $conn = null;
            return true;
        }
        
        $conn = null;
        return false;

    }
}