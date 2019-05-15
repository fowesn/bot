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

        $correct_answer = dbProblem::getAnswer($problem_id);

        unset ($conn);

        return ($user_answer === $correct_answer);
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