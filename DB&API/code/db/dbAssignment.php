<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 18.04.2018
 * Time: 6:26
 */

class dbAssignment
{
    /** Сохраняет в БД факт выдачи задания
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param $problem_id integer Идентификатор задания
     * @throws Exception Внутренняя ошибка
     */
    public static function assignProblem(int $user_id, int $problem_id)
    {
        $conn = dbConnection::getConnection();

        // создание назначения
        $query = $conn->prepare('INSERT INTO  assignment (problem_id, user_id, assignment_last_answer, assigned, correct_answer_provided, correct_answer_requested) 
                                 VALUES (?, ?, NULL, NOW(), FALSE, FALSE )');
        $query->execute(array($problem_id, $user_id));

        $conn = null;
    }


    /** Обновляет строку с назначением
     **
     ** ЗАМЕЧАНИЕ 1: пока пользователь не прислал правильного ответа и не запросил его,
     **              все ответы сохраняются как последние и заносятся в таблицу ответов
     **
     ** ЗАМЕЧАНИЕ 2: значение '1' соответствующей переменной указывает на то событие,
     **              которое произошло ранее другого:
     **              пользователь прислал правильный ответ (correct_answer_provided) или
     **              пользователь запросил правильный ответ (correct_answer_requested);
     **              при этом обе переменные не могут быть равны 1
     **
     ** ЗАМЕЧАНИЕ 3: если в первую очередь пользователь запросил правильный ответ,
     **              то будущие ответы не будут сохранены в таблицу ответов,
     **              а задание никогда не будет засчитано решенным правильно
     **
     ** ЗАМЕЧАНИЕ 4: если в первую очередь пользователь прислал правильный ответ,
     **              то будущие ответы будут сохраняться, как последние присланные,
     **              но не будут сохранены в таблицу ответов
     *
     * @param int $assignment_id идентификатор назначения
     * @param int $update_type способ обновления назначения (смотри константы)
     * @param string|null $answer ответ пользователя (обязателен, если пользователь прислал ответ)
     * @throws Exception Внутренняя ошибка
     */
    public static function updateAssignment(int $assignment_id, int $update_type, string $answer = null)
    {
        $conn = dbConnection::getConnection();

        if ($update_type !== ANSWER_REQUEST)
        {
            if ($answer === null)
            {
                throw new Exception('answer is null for answer check');
            }
        }

        // просил ли пользователь ранее правильный ответ?
        // присылал ли пользователь ранее правильный ответ?
        // оба условия не могут выполняться одновременно
        $query = $conn->prepare('SELECT correct_answer_provided, correct_answer_requested
                                 FROM assignment WHERE assignment_id = ?');
        $query->execute(array($assignment_id));
        $data = $query->fetch();

        $correct_answer_requested = $data['correct_answer_requested'];
        $correct_answer_provided = $data['correct_answer_provided'];

        if ($correct_answer_provided === null)
        {
            throw new Exception('correct_answer_provided is null' );
        }
        if ($correct_answer_requested === null)
        {
            throw new Exception('correct _answer_requested is null' );
        }

        // если пользователь прислал правильный ответ
        if ($update_type === CORRECT_ANSWER)
        {
            // сохраним ответ как последний
            $query = $conn->prepare('UPDATE assignment SET assignment_last_answer = ? WHERE assignment_id = ?');
            $query->execute(array($answer, $assignment_id));

            // при этом если пользователь ранее не присылал правильного ответа и не запрашивал его,
            // то ответ нужно сохранить в таблицу ответов, а задание - засчитать решенным
            if ($correct_answer_requested + $correct_answer_provided !== 1)
            {
                $query = $conn->prepare('INSERT INTO answer (assignment_id, answer, answer_provided) VALUES (?, ?, NOW())');
                $query->execute(array($assignment_id, $answer));

                $query = $conn->prepare('UPDATE assignment SET correct_answer_provided = 1 WHERE assignment_id = ?');
                $query->execute(array($assignment_id));
            }
        }
        // если пользователь прислал неправильный ответ
        elseif ($update_type === WRONG_ANSWER)
        {
            // сохраним ответ как последний
            $query = $conn->prepare('UPDATE assignment SET assignment_last_answer = ? WHERE assignment_id = ?');
            $query->execute(array($answer, $assignment_id));

            // при этом если пользователь ранее не присылал правильного ответа и не запрашивал его,
            // то ответ нужно сохранить в таблицу ответов
            if ($correct_answer_requested + $correct_answer_provided !== 1)
            {
                $query = $conn->prepare('INSERT INTO answer (assignment_id, answer, answer_provided) VALUES (?, ?, NOW())');
                $query->execute(array($assignment_id, $answer));
            }
        }
        // если пользователь запросил ответ
        elseif ($update_type === ANSWER_REQUEST)
        {
            // и при этом ранее не присылал правильного ответа и не запрашивал его,
            // то нужно засчитать задание нерешенным
            if ($correct_answer_requested + $correct_answer_provided !== 1)
            {
                $query = $conn->prepare('UPDATE assignment SET correct_answer_requested = 1 WHERE assignment_id = ?');
                $query->execute(array($assignment_id));
            }
        }

        $conn = null;
    }


    /** Возвращает случайно выбранное задание для пользователя из еще не выданных
     ** Возможна фильтрация по теме задания или по номеру задания в КИМе
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param null $filter Тема задания (string), номер задания в киме (integer), иначе_null
     * @return integer Идентификатор еще не назначенного задания
     * @throws APIException Неверный номер задания в КИМе/тема задания; невыданные задания кончились
     * @throws Exception Внутренняя ошибка
     */
    public static function getUnassignedProblem(int $user_id, $filter)
    {
        $conn = dbConnection::getConnection();

        /*
         * случайное задание
         */
        if (is_null($filter)) {
            // количество нерешенных заданий
            $query = $conn->prepare('SELECT COUNT(*) FROM problem, user
                                     WHERE user.user_id = ?
                                     AND user.year_range <= problem.problem_year
                                     AND problem.problem_id IN (
                                         SELECT problem.problem_id FROM problem LEFT JOIN assignment 
                                         ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                         WHERE assignment.problem_id IS NULL)');

            $query->execute(array($user_id, $user_id));
            // если не осталось нерешенных заданий
            if (($rows = (int)($query->fetchColumn())) === 0) {
                throw new APIException(OUT_OF_PROBLEMS, OUT_OF_PROBLEMS_MSG, 200);
            }

            // во избежание notice о том, что в bindParam() нужно подставлять только переменные
            $rand = mt_rand(0, $rows - 1);

            $query = $conn->prepare('SELECT problem.problem_id FROM problem, user
                                     WHERE user.user_id = ? 
                                     AND user.year_range <= problem.problem_year
                                     AND problem.problem_id IN (
                                         SELECT problem.problem_id FROM problem LEFT JOIN assignment 
                                         ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                         WHERE assignment.problem_id IS NULL)
                                     LIMIT ?, 1');
            // при подстановке аргументов с помощью execute в последний placeholder почему-то попадает строка, а не число
            $query->bindParam(1, $user_id, PDO::PARAM_INT);
            $query->bindParam(2, $user_id, PDO::PARAM_INT);
            $query->bindParam(3, $rand, PDO::PARAM_INT);
            $query->execute();
            $problem_id = $query->fetch()['problem_id'];
        }

        /*
         * случайное задание по номеру в КИМе
         */
        elseif (ctype_digit($filter)) {
            // проверка существования номера в КИМе
            $query = $conn->prepare('SELECT exam_item_id FROM exam_item WHERE exam_item_number  = ?');
            $query->execute(array($filter));
            // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
            // попытка обратиться к FALSE по ключу вернет null
            if ($query->fetch()['exam_item_id'] === null) {
                throw new APIException(EXAM_ITEM_NOT_FOUND, EXAM_ITEM_NOT_FOUND_MSG, 422);
            }

            // количество нерешенных заданий с указанным номером в КИМе
            $query = $conn->prepare('SELECT COUNT(*) FROM problem, user
                                     WHERE user.user_id = ?
                                     AND user.year_range <= problem.problem_year
                                     AND problem.problem_id IN (
                                         SELECT problem.problem_id FROM problem
                                         INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                         LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                         WHERE assignment.problem_id IS NULL)');
            $query->execute(array($user_id, $filter, $user_id));
            // если не осталось нерешенных заданий с указанным номером в КИМе
            if (($rows = (int)($query->fetchColumn())) === 0) {
                throw new APIException(OUT_OF_PROBLEMS_BY_NUMBER, OUT_OF_PROBLEMS_BY_NUMBER_MSG, 200);
            }

            // во избежание notice о том, что в bindParam() нужно подставлять только переменные
            $rand = mt_rand(0, $rows - 1);

            $query = $conn->prepare('SELECT problem.problem_id FROM problem, user
                                     WHERE user.user_id = ? 
                                     AND user.year_range <= problem.problem_year
                                     AND problem.problem_id IN (
                                         SELECT problem.problem_id FROM problem 
                                         INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                         LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                         WHERE assignment.problem_id IS NULL)
                                     LIMIT ?, 1');
            // при подстановке аргументов с помощью execute в последний placeholder почему-то попадает строка, а не число
            $query->bindParam(1, $user_id, PDO::PARAM_INT);
            $query->bindParam(2, $filter, PDO::PARAM_INT);
            $query->bindParam(3, $user_id, PDO::PARAM_INT);
            $query->bindParam(4, $rand, PDO::PARAM_INT);
            $query->execute();
            $problem_id = $query->fetch()['problem_id'];
        }

        /*
         * случайное задание по теме
         */
        else {
            // проверка существования темы
            $query = $conn->prepare('SELECT problem_type_id FROM problem_type WHERE problem_type_code  = ?');
            $query->execute(array($filter));
            // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
            // попытка обратиться к FALSE по ключу вернет null
            if ($query->fetch()['problem_type_id'] === null) {
                throw new APIException(PROBLEM_TYPE_NOT_FOUND, PROBLEM_TYPE_NOT_FOUND_MSG, 422);
            }

            // количество нерешенных заданий по указанной теме
            $query = $conn->prepare('SELECT COUNT(*) FROM problem, user
                                     WHERE user.user_id = ?
                                     AND user.year_range <= problem.problem_year
                                     AND problem.problem_id IN (
                                         SELECT problem.problem_id FROM problem
                                         INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                         LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                         WHERE assignment.problem_id IS NULL)');
            $query->execute(array($user_id, $filter, $user_id));
            // если не осталось нерешенных заданий по указанной теме
            if (($rows = (int)($query->fetchColumn())) === 0) {
                throw new APIException(OUT_OF_PROBLEMS_BY_TYPE, OUT_OF_PROBLEMS_BY_TYPE_MSG, 200);
            }

            // во избежание notice о том, что в bindParam() нужно подставлять только переменные
            $rand = mt_rand(0, $rows - 1);

            $query = $conn->prepare('SELECT problem.problem_id FROM problem, user
                                     WHERE user.user_id = ? 
                                     AND user.year_range <= problem.problem_year
                                     AND problem.problem_id IN (
                                         SELECT problem.problem_id FROM problem 
                                         INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                         LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                         WHERE assignment.problem_id IS NULL)
                                     LIMIT ?, 1');
            // при подстановке аргументов с помощью execute в последний placeholder почему-то попадает строка, а не число
            $query->bindParam(1, $user_id, PDO::PARAM_INT);
            $query->bindParam(2, $filter, PDO::PARAM_STR);
            $query->bindParam(3, $user_id, PDO::PARAM_INT);
            $query->bindParam(4, $rand, PDO::PARAM_INT);
            $query->execute();
            $problem_id = $query->fetch()['problem_id'];
        }

        $conn = null;

        return $problem_id;
    }


    /** Проверяет, было ли назначено указанное задание пользователю
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param $problem_id integer Идентификатор задания
     * @return bool true, если_задание было назначено, false - иначе
     * @throws Exception Внутренняя ошибка
     */
    public static function isAssigned(int $user_id, int $problem_id)
    {
        $conn = dbConnection::getConnection();

        // проверка существования пользователя
//        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
//        $query->execute(array($user_id));
//        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
//        // попытка обратиться к FALSE по ключу вернет null
//        if ($query->fetch()['user_id'] === null) {
//            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
//        }
//
//        // проверка существования задания
//        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
//        $query->execute(array($problem_id));
//        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
//        // попытка обратиться к FALSE по ключу вернет null
//        if ($query->fetch()['problem_id'] === null) {
//            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
//        }

        $query = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $query->execute(array($problem_id, $user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['assignment_id'] === null) {
            return false;
        }

        $conn = null;

        return true;
    }


    /** Возвращает идентификатор назначения по идентификатору задания и
     ** глобальному идентификатору пользователя
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param $problem_id integer Идентификатор задания
     * @return integer Идентификатор назначения
     * @throws Exception Внутренняя ошибка
     */
    public static function getAssignmentId(int $user_id, int $problem_id)
    {
        $conn = dbConnection::getConnection();

        $query = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $query->execute(array($problem_id, $user_id));
        $assignment_id = $query->fetch()['assignment_id'];

        $conn = null;

        return $assignment_id;
    }


    /** Возвращает число выданных пользователю заданий, выделяя число решенных, нерешенных и тех, для которых был запрошен правильный ответ
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @return array Количество соответствующих заданий
     * @throws Exception Внутренняя ошибка
     */
    public static function getAssignmentsCount(int $user_id)
    {
        $conn = dbConnection::getConnection();

        $data = [
            'overall' => 0,
            'solved' => 0,
            'answer_requested' => 0,
            'unsolved' => 0
        ];

        //подсчет числа всех выданных заданий
        $query = $conn->prepare('SELECT COUNT(*) FROM assignment 
                                 WHERE user_id = ?');
        $query->execute(array($user_id));
        $data['overall'] = $query->fetchColumn();

        // подсчет числа выданных заданий, которые были решены
        $query = $conn->prepare('SELECT COUNT(*) FROM assignment 
                                 WHERE user_id = ? AND correct_answer_provided = 1');
        $query->execute(array($user_id));
        $data['solved'] = $query->fetchColumn();

        // подсчет числа выданных заданий, для которых был запрошен правильный ответ
        $query = $conn->prepare('SELECT COUNT(*) FROM assignment
                                 WHERE user_id = ? AND correct_answer_requested = 1');
        $query->execute(array($user_id));
        $data['answer_requested'] = $query->fetchColumn();

        // подсчет числа выданных заданий, которые не были решены
        $query = $conn->prepare('SELECT COUNT(*) FROM assignment 
                                 WHERE user_id = ? 
                                 AND correct_answer_provided = 0
                                 AND correct_answer_requested = 0');
        $query->execute(array($user_id));
        $data['unsolved'] = $query->fetchColumn();

        $conn = null;

        return $data;
    }


    /** Возвращает информацию о назначении пользователя: количество присланных ответов, наличие правильного ответа среди присланных, факт запроса правильного ответа
     *
     * @param $assignment_id integer Идентификатор назначения
     * @return array Информация о назначении
     * @throws Exception Внутренняя ошибка
     */
    public static function getAssignmentData(int $assignment_id)
    {
        $conn = dbConnection::getConnection();

        $data = array(
            'answers_provided' => null,
            'correct_answer_provided' => null,
            'correct_answer_requested' => null
        );

        // проверка того, что пользователь прислал правильный ответ и запросил правильный ответ (заодно поиск assignment_id для следующего шага)
        $query = $conn->prepare('SELECT assignment.correct_answer_provided, assignment.correct_answer_requested 
                                 FROM assignment
                                 WHERE assignment.assignment_id = ?');
        $query->execute(array($assignment_id));
        $data = $query->fetch();
        $data['correct_answer_provided'] = (bool)$data['correct_answer_provided'];
        $data['correct_answer_requested'] = (bool)$data['correct_answer_requested'];

        // подсчет количества присланных ответов
        $query = $conn->prepare('SELECT COUNT(*) FROM answer
                                 WHERE answer.assignment_id = ?');
        $query->execute(array($assignment_id));
        $data['answers_provided'] = (int)$query->fetchColumn();

        $conn = null;

        return $data;
    }


    public static function getUnsolvedProblems(int $user_id)
    {
        $conn = dbConnection::getConnection();

        $unsolved = array();

        $query = $conn->prepare('SELECT assignment.problem_id FROM assignment
                              WHERE assignment.correct_answer_requested = 0 
                              AND assignment.correct_answer_provided = 0
                              AND assignment.user_id = ?');
        $query->execute(array($user_id));
        while ($row = $query->fetch())
        {
            $unsolved[] = $row['problem_id'];
        }

        $conn = null;

        return $unsolved;
    }
}