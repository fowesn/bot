<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 02.05.2018
 * Time: 13:21
 */

/** Интерфейс проверки ответа пользователя
 *
 * Interface checkAnswerTemplate
 */
interface checkAnswerInterface
{
    public static function checkAnswer($problem_id, $user_answer);
}



/** Класс отвечает за проверку заданий с кратким ответом (часть B)
 *
 * Class checkShortAnswer
 */
class checkShortAnswer implements checkAnswerInterface
{
    /** Проверяет правильность ответа пользователя,
     ** сравнивая ответ задания в БД с ответом, присланным пользователем
     *
     * @param $problem_id integer Идентификатор задания
     * @param $user_answer string Ответ пользователя
     * @return bool true, если ответ пользователя правильный, false - иначе
     * @throws Exception Внутренняя ошибка
     */
    public static function checkAnswer($problem_id, $user_answer)
    {
        $conn = dbConnection::getConnection();

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        if (is_null($user_answer))
        {
            throw new Exception('user_answer is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        $correct_answer = dbProblem::getAnswer($problem_id);

        unset ($conn);

        // проверка правильности ответа пользователя
        if ($user_answer == $correct_answer)
        {
            return true;
        }

        return false;
    }
}



/** Класс отвечает за проверку заданий с развернутым ответом (часть C)
 *
 * Class checkDetailedAnswer
 */
class checkDetailedAnswer implements checkAnswerInterface
{
    public static function checkAnswer($problem_id, $user_answer)
    {
        // TODO: Implement checkAnswer() method.
    }
}

?>