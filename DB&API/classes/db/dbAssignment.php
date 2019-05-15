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

        unset($conn);
    }


    /** Сохраняет ответ пользователя в БД, проверяя его правильность
     **
     ** ЗАМЕЧАНИЕ 1: если пользователь запросил правильный ответ ранее,
     **              то данное задание уже никогда не будет засчитано как решенное правильно;
     **
     ** ЗАМЕЧАНИЕ 2: если ранее пользователь уже дал правильный ответ,
     **              то текущий ответ сохраняется как последний, но не заносится в таблицу ответов;
     **              при этом правильность ответа проверяется
     *
     * @param int $user_id Глобальный идентификатор пользователя
     * @param int $problem_id Идентификатор задания
     * @param string $user_answer Ответ пользователя
     * @return array Ответ правильный; ответ ранее запрошен
     * @throws Exception Внутренняя ошибка
     */
    public static function assignAnswer(int $user_id, int $problem_id, string $user_answer)
    {
        $conn = dbConnection::getConnection();

        $assignment_id = self::getAssignmentId($user_id, $problem_id);

        // сохранение ответа пользователя в назначении - НАДО ВСЕГДА
        $query = $conn->prepare('UPDATE assignment SET assignment_last_answer = ? WHERE assignment_id = ?');
        $query->execute(array($user_answer, $assignment_id));

        // проверка правильности ответа пользователя - НАДО ВСЕГДА
        $answer_correct = checkShortAnswer::checkAnswer($problem_id, $user_answer);

        // просил ли пользователь ранее правильный ответ?
        $query = $conn->prepare('SELECT correct_answer_requested FROM assignment WHERE assignment_id = ?');
        $query->execute(array($assignment_id));
        $correct_answer_requested = ($query->fetchColumn() === 1);

        // если пользователь не просил правильного ответа ранее
        if (!$correct_answer_requested)
        {
            // то присылал ли он ранее правильный ответ?
            $query = $conn->prepare('SELECT correct_answer_provided FROM assignment WHERE assignment_id = ?');
            $query->execute(array($assignment_id));
            $correct_answer_provided = ($query->fetchColumn() === 1);

            // если нет, то дополнительно сохраняем его ответ в таблицу с ответами
            if (!$correct_answer_provided)
            {
                $query = $conn->prepare('INSERT INTO answer (assignment_id, answer, answer_provided) VALUES (?, ?, NOW())');
                $query->execute(array($assignment_id, $user_answer));

                if ($answer_correct)
                {
                    // а если ответ правильный, то сохраним и это
                    $query = $conn->prepare('UPDATE assignment SET correct_answer_provided = 1 WHERE assignment_id = ?');
                    $query->execute(array($assignment_id));
                }
            }
        }

        unset ($conn);

        return array($answer_correct, $correct_answer_requested);
    }


    /** Возвращает случайно выбранное задание для пользователя из еще не решенных
     ** Возможна фильтрация по теме задания или по номеру задания в КИМе
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param null $filter Тема задания (string), номер задания в киме (integer), иначе_null
     * @return integer Идентификатор еще не назначенного задания
     * @throws APIException Неверный номер задания в КИМе/тема задания; невыданные задания кончились
     * @throws Exception Внутренняя ошибка
     */
    public static function getUnassignedProblem(int $user_id, $filter = null)
    {
        $conn = dbConnection::getConnection();

        // проверка существования пользователя
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null) {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }


        /*
         * случайное задание
         */
        if (is_null($filter)) {
            // количество нерешенных заданий
            $query = $conn->prepare('SELECT COUNT(*) FROM problem LEFT JOIN assignment 
                                     ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL');

            $query->execute(array($user_id));
            // если не осталось нерешенных заданий
            if (($rows = (int)($query->fetchColumn())) === 0) {
                throw new APIException(OUT_OF_PROBLEMS, OUT_OF_PROBLEMS_MSG, 200);
            }

            $query = $conn->prepare('SELECT problem.problem_id as problem FROM problem LEFT JOIN assignment 
                                     ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL LIMIT ?, 1');
            $query->execute(array($user_id, mt_rand(0, $rows - 1)));
            $problem_id = $query->fetch()['problem'];
        }

        /*
         * случайное задание по номеру в КИМе
         */
        elseif (is_int($filter)) {
            // проверка существования номера в КИМе
            $query = $conn->prepare('SELECT exam_item_id FROM exam_item WHERE exam_item_number  = ?');
            $query->execute(array($filter));
            // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
            // попытка обратиться к FALSE по ключу вернет null
            if ($query->fetch()['exam_item_id'] === null) {
                throw new APIException(-1, EXAM_ITEM_NOT_FOUND_MSG, 422);
            }

            // количество нерешенных заданий с указанным номером в КИМе
            $query = $conn->prepare('SELECT COUNT(*) FROM problem 
                                     INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                     LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL');
            $query->execute(array($filter, $user_id));
            // если не осталось нерешенных заданий с указанным номером в КИМе
            if (($rows = (int)($query->fetchColumn())) === 0) {
                throw new APIException(OUT_OF_PROBLEMS_BY_NUMBER, OUT_OF_PROBLEMS_BY_NUMBER_MSG, 200);
            }

            $query = $conn->prepare('SELECT problem.problem_id as problem FROM problem 
                                     INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                     LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL LIMIT ?, 1');
            $query->execute(array($filter, $user_id, mt_rand(0, $rows - 1)));
            $problem_id = $query->fetch()['problem'];
        }

        /*
         * случайное задание по теме
         */
        elseif (is_string($filter)) {
            // проверка существования темы
            $query = $conn->prepare('SELECT problem_type_id FROM problem_type WHERE problem_type_code  = ?');
            $query->execute(array($filter));
            // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
            // попытка обратиться к FALSE по ключу вернет null
            if ($query->fetch()['problem_type_id'] === null) {
                throw new APIException(-1, PROBLEM_TYPE_NOT_FOUND_MSG, 422);
            }

            // количество нерешенных заданий по указанной теме
            $query = $conn->prepare('SELECT COUNT(*) FROM problem 
                                     INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                     LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL');
            $query->execute(array($filter, $user_id));
            // если не осталось нерешенных заданий по указанной теме
            if (($rows = (int)($query->fetchColumn())) === 0) {
                throw new APIException(OUT_OF_PROBLEMS_BY_TYPE, OUT_OF_PROBLEMS_BY_TYPE_MSG, 200);
            }

            $query = $conn->prepare('SELECT problem.problem_id as problem, problem.problem_statement FROM problem 
                                     INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                     LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL LIMIT ?, 1');
            $query->execute(array($filter, $user_id, mt_rand(0, $rows - 1)));
            $problem_id = $query->fetch()['problem'];
        }

        unset($conn);

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
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null) {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['problem_id'] === null) {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        $query = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $query->execute(array($problem_id, $user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['assignment_id'] === null) {
            return true;
        }

        unset($conn);

        return false;
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

        unset($conn);

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
        $query = $conn->prepare('SELECT COUNT(*) FROM assignment WHERE user_id = ?');
        $query->execute(array($user_id));
        $data['overall'] = $query->fetchColumn();


        // подсчет числа выданных заданий, которые были решены
        $query = $conn->prepare('SELECT COUNT(*) FROM assignment WHERE user_id = ? AND correct_answer_provided = 1');
        $query->execute(array($user_id));
        $data['solved'] = $query->fetchColumn();

        // подсчет числа выданных заданий, которые не были решены
        $query = $conn->prepare('SELECT COUNT(*) FROM assignment WHERE user_id = ? AND correct_answer_provided = 0');
        $query->execute(array($user_id));
        $data['unsolved'] = $query->fetchColumn();

        // подсчет числа выданных заданий, для которых был запрошен правильный ответ
        $query = $conn->prepare('SELECT COUNT(*) FROM assignment WHERE user_id = ? AND correct_answer_requested = 1');
        $query->execute(array($user_id));
        $data['answer_requested'] = $query->fetchColumn();

        unset($conn);

        return $data;
    }


    /** Возвращает информацию о назначении пользователя: тема задания, номер задания в КИМе, год
     ** количество присланных ответов, наличие правильного ответа среди присланных, факт запроса правильного ответа
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param $problem_id integer Идентификатор задания
     * @return array Информация о назначении
     * @throws Exception Внутренняя ошибка
     */
    public static function getAssignmentData(int $user_id, int $problem_id)
    {
        $conn = dbConnection::getConnection();

        $data = array(
            'exam_number' => null,
            'problem_type' => null,
            'year' => null,
            'answers_provided' => null,
            'correct_answer_provided' => null,
            'correct_answer_requested' => null
        );

        // получение темы задания
        $query = $conn->prepare('SELECT problem_type.problem_type_code FROM problem_type 
                                 WHERE problem.problem_type_id = problem_type.problem_type_id
                                 AND problem.problem_id = ?');
        $query->execute(array($problem_id));
        $data['problem_type'] = $query->fetch()['problem_type_code'];


        // получение номера задания в КИМе
        $query = $conn->prepare('SELECT exam_item.exam_item_number FROM exam_item 
                                 WHERE exam_item.exam_item_id = problem.exam_item_id
                                 AND problem.problem_id = ?');
        $query->execute(array($problem_id));
        $data['exam_number'] = $query->fetch()['exam_item_number'];

        // получение года задания
        $query = $conn->prepare('SELECT problem.problem_year FROM problem 
                                 WHERE problem.problem_id = ?');
        $query->execute(array($problem_id));
        $data['year'] = $query->fetch()['problem_year'];

        // проверка того, что пользователь прислал правильный ответ и запросил правильный ответ (заодно поиск assignment_id для следующего шага)
        $query = $conn->prepare('SELECT assignment.assignment_id, assignment.correct_answer_provided, assignment.correct_answer_requested 
                                 FROM assignment
                                 WHERE assignment.problem_id = ? AND assignment.user_id = ?');
        $query->execute(array($problem_id, $user_id));
        $assignment = $query->fetch()['assignment_id'];
        $data['correct_answer_provided'] = (bool)$query->fetch()['correct_answer_provided'];
        $data['correct_answer_requested'] = (bool)$query->fetch()['correct_answer_requested'];

        // подсчет количества присланных ответов
        $query = $conn->prepare('SELECT COUNT(*) FROM answer
                                 WHERE answer.assignment_id = ?');
        $query->execute(array($assignment));
        $data['answers_provided'] = $query->fetchColumn();

        unset($conn);

        return $data;
    }


    public static function getAssignments(int $user_id)
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

        unset($conn);

        return $unsolved;
    }

}