<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbResult
{
    /**
     * @param $user_id Global id of the user who requested correct answer
     * @param $problem_id Id of a problem which correct answer is requested
     * @return mixed Correct answer
     * @throws Exception System error
     * @throws UserExceptions Depend on user's actions
     */
    public static function getAnswer ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        if ($user_id === null || !is_numeric($user_id))
        {
            throw new Exception('Invalid parameter: user_id is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        if ($problem_id === null || !is_numeric($problem_id))
        {
            throw new Exception('Invalid parameter: problem_id is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        // User can't get correct answer of the task he wasn't assigned to
        $stmt = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $stmt->execute(array($problem_id, $user_id));
        if (($assignment_id = $stmt->fetch()['assignment_id']) === null)
        {
            throw new UserExceptions('Вы не получали задания, для которого просите правильный ответ!', 4);
        }

        $stmt = $conn->prepare('SELECT problem_answer FROM problem WHERE problem_id = ?');
        $stmt->execute(array($problem_id));
        $answer = $stmt->fetch()['problem_answer'];
        
        $conn = null;
        return $answer;
    }

    /**
     * @param $user_id
     * @param $problem_id
     * @return array Contains solution stored in resources
     * @throws Exception System error
     * @throws UserExceptions Depend on user's actions
     */
    public static function getSolution ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        if ($user_id === null || !is_numeric($user_id))
        {
            throw new Exception('Invalid parameter: user_id is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        if ($problem_id === null || !is_numeric($problem_id))
        {
            throw new Exception('Invalid parameter: problem_id is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__, 500);
        }

        // User can't get solution of the task he wasn't assigned to
        $stmt = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $stmt->execute(array($problem_id, $user_id));
        if (($assignment_id = $stmt->fetch()['assignment_id']) === null)
        {
            throw new UserExceptions('Вы не получали задания, для которого просите разбор!', 4);
        }

        $stmt = $conn->prepare('SELECT problem_solution FROM problem WHERE problem_id = ?');
        $stmt->execute(array($problem_id));

        return dbResource::getPreferredResource($user_id, $stmt->fetch()['problem_solution']);

    }
}