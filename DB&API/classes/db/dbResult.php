<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbResult
{
    public static function getAnswer ($problem_id)
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->prepare('SELECT problem_answer FROM problem WHERE problem_id = ?');
        $stmt->execute(array($problem_id));

        if (($correct_answer = $stmt->fetch()['problem_answer']) === null)
        {
            /** @throws  ?Exception  Specified problem_id does not exist */
        }

        return $correct_answer;
    }

    public static function getSolution ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        // Check whether the stated user exists
        $user_check = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $user_check->execute(array($user_id));
        if ($user_check->fetch() === null)
        {
            /** @throws ?Exception  Specified user_id does not exist */
        }

        $stmt = $conn->prepare('SELECT problem_solution FROM problem WHERE problem_id = ?');
        $stmt->execute(array($problem_id));

        if (($resource_collection_id = $stmt->fetch()['problem_solution']) === null)
        {
            /** @throws ?Exception Specified problem_id does not exist */
        }

        return dbResource::getPreferredResource($user_id, $resource_collection_id);

    }
}