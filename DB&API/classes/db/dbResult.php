<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbResult extends dbConnection
{
    public static function getAnswer ($problem_id)
    {
        $conn = dbConnection::getConnection();
        $stmt = $conn->prepare('SELECT problem_answer FROM problem WHERE problem_id = ?');
        $stmt->execute(array($problem_id));
        $correct_answer = $stmt->fetch();
        return $correct_answer['problem_answer'];
    }

    public static function getSolution ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

    }
}