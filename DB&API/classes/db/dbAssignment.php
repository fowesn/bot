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
     * @return bool
     * @throws Exception Внутренняя_ошибка
     */
    public static function assignProblem ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        // проверка существования пользователя
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null)
        {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // проверка того, что задание еще не было назначено
        $query = $conn->prepare('SELECT assignment_id FROM assignment 
                                 WHERE problem_id = ? AND user_id = ?');
        $query->execute(array($problem_id, $user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if (($query->fetch()['assignment_id']) !== null)
        {
            throw new Exception('Problem_id ' . $problem_id . ' has already been assigned to user ' . $user_id . '; record id = '. $query->fetch()['assignment_id'], 500);
        }


        // создание назначения
        $query = $conn->prepare('INSERT INTO  assignment (problem_id, user_id, assignment_last_answer, assigned, correct_answer_provided) 
                                 VALUES (?, ?, NULL, NOW(), FALSE )');
        $query->execute(array($problem_id, $user_id));

        unset($conn);
    }


    /** Сохраняет ответ пользователя в БД, проверяя его правильность
     ** ЗАМЕЧАНИЕ: если ранее пользователь уже дал правильный ответ,
     **            то текущий ответ сохраняется как последний, но не заносится в таблицу ответов;
     **            при этом правильность ответа проверяется
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param $problem_id integer Идентификатор задания
     * @param $user_answer string Ответ пользователя
     * @throws UserException Задание не было выдано пользователю
     * @throws Exception Внутренняя ошибка
     */
    public static function assignAnswer ($user_id, $problem_id, $user_answer)
    {
        $conn = dbConnection::getConnection();

        // проверка существования пользователя
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null)
        {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        if ($user_answer === null)
        {
            throw new Exception('user_answer is NULL; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }


        if (!self::isAssigned($user_id, $problem_id))
        {
            throw new UserException(PROBLEM_NOT_ASSIGNED, PROBLEM_NOT_ASSIGNED_MSG, 200);
        }
        else
        {
            $assignment_id = self::getAssignmentId($user_id, $problem_id);

            // сохранение ответа пользователя в назначении
            $query = $conn->prepare('UPDATE assignment SET assignment_last_answer = ? WHERE assignment_id = ?');
            $query->bindParam('1', $user_answer);
            $query->bindParam('2', $assignment_id);
            $query->execute();

            // проверка того, что пользователь дал правильный ответ ранее
            $query = $conn->prepare('SELECT correct_answer_provided FROM assignment WHERE assignment_id = ?');
            $query->execute(array($assignment_id));

            // правильный ответ не был дан ранее
            if ($query->fetchColumn() === 0)
            {
                // если ранее пользователь не дал правильный ответ, то сохраняем ответ
                // если ранее пользователь уже дал правильный ответ, то он не сохраняется
                $query = $conn->prepare('INSERT INTO answer (assignment_id, answer, answer_provided) VALUES (?, ?, NOW())');
                $query->bindParam('1', $assignment_id, PDO::PARAM_INT);
                $query->bindParam('2', $user_answer, PDO::PARAM_STR);
            }


            // проверка правильности ответа пользователя
            if (checkShortAnswer::checkAnswer($problem_id, $user_answer))
            {
                $query = $conn->prepare('UPDATE assignment SET correct_answer_provided = 1 WHERE assignment_id = ?');
                $query->execute(array($assignment_id));
            }

            unset ($conn);
        }
    }



    /** Возвращает случайно выбранное задание для пользователя из еще не решенных
     ** Возможна фильтрация по теме задания или по номеру задания в КИМе
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param null $filter Тема задания (string), номер задания в киме (integer), иначе_null
     * @return integer Идентификатор еще не назначенного задания
     * @throws UserException Неверный номер задания в КИМе/тема задания; невыданные задания кончились
     * @throws Exception Внутренняя ошибка
     */
    public static function getUnassignedProblem ($user_id, $filter = null)
    {
        $conn = dbConnection::getConnection();

        // проверка существования пользователя
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null)
        {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }


        /*
         * случайное задание
         */
        if (is_null($filter))
        {
            // количество нерешенных заданий
            $query = $conn->prepare('SELECT COUNT(*) FROM problem LEFT JOIN assignment 
                                     ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL');

            $query->execute(array($user_id));
            // если не осталось нерешенных заданий
            if (($rows = (int)($query->fetchColumn())) === 0)
            {
                throw new UserException(OUT_OF_PROBLEMS, OUT_OF_PROBLEMS_MSG, 200);
            }

            $query = $conn->prepare('SELECT problem.problem_id as problem FROM problem LEFT JOIN assignment 
                                     ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL LIMIT ?, 1');
            $query->bindParam(1, $user_id, PDO::PARAM_INT);
            $query->bindParam(2, mt_rand(0, $rows - 1), PDO::PARAM_INT);
            $query->execute();
            $problem_id = $query->fetch()['problem'];
        }


        /*
         * случайное задание по номеру в КИМе
         */
        elseif (is_int($filter))
        {
            // проверка существования номера в КИМе
            $query = $conn->prepare('SELECT exam_item_id FROM exam_item WHERE exam_item_number  = ?');
            $query->execute(array($filter));
            // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
            // попытка обратиться к FALSE по ключу вернет null
            if ($query->fetch()['exam_item_id'] === null)
            {
                throw new UserException(EXAM_ITEM_NOT_FOUND, EXAM_ITEM_NOT_FOUND_MSG, 200);
            }

            // количество нерешенных заданий с указанным номером в КИМе
            $query = $conn->prepare('SELECT COUNT(*) FROM problem 
                                     INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                     LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL');
            $query->execute(array($filter, $user_id));
            // если не осталось нерешенных заданий с указанным номером в КИМе
            if (($rows = (int)($query->fetchColumn())) === 0)
            {
                throw new UserException(OUT_OF_PROBLEMS_BY_NUMBER,OUT_OF_PROBLEMS_BY_NUMBER_MSG, 200);
            }

            $query = $conn->prepare('SELECT problem.problem_id as problem FROM problem 
                                     INNER JOIN exam_item ON (exam_item.exam_item_id = problem.exam_item_id AND exam_item.exam_item_number = ?)
                                     LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL LIMIT ?, 1');
            $query->bindParam('1', $filter, PDO::PARAM_INT);
            $query->bindParam('2', $user_id, PDO::PARAM_INT);
            $query->bindParam('3', mt_rand(0, $rows - 1), PDO::PARAM_INT);
            $query->execute();
            $problem_id = $query->fetch()['problem'];
        }


        /*
         * случайное задание по теме
         */
        elseif (is_string($filter))
        {
            // проверка существования темы
            $query = $conn->prepare('SELECT problem_type_id FROM problem_type WHERE problem_type_code  = ?');
            $query->execute(array($filter));
            // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
            // попытка обратиться к FALSE по ключу вернет null
            if ($query->fetch()['problem_type_id'] === null)
            {
                throw new UserException(PROBLEM_TYPE_NOT_FOUND, PROBLEM_TYPE_NOT_FOUND_MSG, 200);
            }

            // количество нерешенных заданий по указанной теме
            $query = $conn->prepare('SELECT COUNT(*) FROM problem 
                                     INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                     LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL');
            $query->execute(array($filter, $user_id));
            // если не осталось нерешенных заданий по указанной теме
            if (($rows = (int)($query->fetchColumn())) === 0)
            {
                throw new UserException(OUT_OF_PROBLEMS_BY_TYPE,OUT_OF_PROBLEMS_BY_TYPE_MSG, 200);
            }

            $query = $conn->prepare('SELECT problem.problem_id as problem, problem.problem_statement FROM problem 
                                     INNER JOIN problem_type ON (problem_type.problem_type_id = problem.problem_type_id AND problem_type.problem_type_code  = ?) 
                                     LEFT JOIN assignment ON (assignment.problem_id = problem.problem_id AND assignment.user_id = ?) 
                                     WHERE assignment.problem_id IS NULL LIMIT ?, 1');
            $query->bindParam('1', $filter, PDO::PARAM_STR, 255);
            $query->bindParam('2', $user_id, PDO::PARAM_INT);
            $query->bindParam('3', mt_rand(0, $rows - 1), PDO::PARAM_INT);
            $query->execute();
            $problem_id = $query->fetch()['problem'];
        }


        else
        {
            throw new Exception('filter = ' . $filter . '; Method: ' . __METHOD__ . '; Line: ' . __LINE__);
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
    public static function isAssigned ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        // проверка существования пользователя
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null)
        {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }


        $query = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $query->execute(array($problem_id, $user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['assignment_id'] === null)
        {
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
    public static function getAssignmentId ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        // проверка существования пользователя
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null)
        {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query->execute(array($problem_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }


        $query = $conn->prepare('SELECT assignment_id FROM assignment WHERE problem_id = ? AND user_id = ?');
        $query->execute(array($problem_id, $user_id));
        if (($assignment_id = $query->fetch()['assignment_id']) === null)
        {
            throw new Exception('assignment_id not found in \'assignment\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        unset($conn);

        return $assignment_id;
    }


    /** Возвращает число выданных пользователю заданий
     ** Возможна фильтрация по наличию правильного ответа пользователя
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param string $filter Все_задания (overall), решенные_правильно (solved), не решенные правильно (unsolved)
     * @return integer Количество_выданных_заданий_в_соответствии_с_фильтром
     * @throws Exception Внутренняя_ошибка
     */
    public static function getAssignmentsCount ($user_id, $filter = 'overall')
    {
        $conn = dbConnection::getConnection();


        // проверка наличия пользователя
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null)
        {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        /*
         * подсчет числа всех выданных заданий
         */
        if ($filter === 'overall')
        {
            $query = $conn->prepare('SELECT COUNT(*) FROM assignment WHERE user_id = ?');
            $query->execute(array($user_id));
            $count = $query->fetchColumn();
        }


        /*
         * подсчет числа выданных заданий, которые были решены
         */
        elseif ($filter === 'solved')
        {
            $query = $conn->prepare('SELECT COUNT(*) FROM assignment WHERE user_id = ? AND correct_answer_provided = 1');
            $query->execute(array($user_id));
            $count = $query->fetchColumn();
        }


        /*
         * подсчет числа выданных заданий, которые не были решены
         */
        elseif ($filter === 'unsolved')
        {
            $query = $conn->prepare('SELECT COUNT(*) FROM assignment WHERE user_id = ? AND correct_answer_provided = 0');
            $query->execute(array($user_id));
            $count = $query->fetchColumn();
        }



        else
        {
            throw new Exception('filter = ' . $filter . '; Method: ' . __METHOD__ . '; Line: ' . __LINE__);
        }

        unset($conn);

        return $count;
    }



    /** Возвращает информацию о назначении пользователя: тема задания, номер задания в КИМе,
     ** количество присланных ответов, наличие правильного ответа среди присланных
     *
     * @param $user_id integer Глобальный идентификатор пользователя
     * @param $problem_id integer Идентификатор задания
     * @return array Информация о назначении
     * @throws UserException Задание не было выдано пользователю
     * @throws Exception Внутренняя ошибка
     */
    public static function getAssignmentData ($user_id, $problem_id)
    {
        $conn = dbConnection::getConnection();

        $data = array(
            'exam_number' => null,
            'problem_type' => null,
            'answers_provided' => null,
            'correct_answer_provided' => null
        );

        // проверка существования пользователя
        $query = $conn->prepare('SELECT user_id FROM user WHERE user_id  = ?');
        $query->execute(array($user_id));
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['user_id'] === null)
        {
            throw new Exception('user_id ' . ($user_id === null ? 'NULL' : $user_id) . ' not found in \'user\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }

        // проверка существования задания
        $query = $conn->prepare('SELECT problem_id FROM problem WHERE problem_id = ?');
        $query = $conn->bindParam('1', $problem_id, PDO::PARAM_INT);
        $query->execute();
        // fetch() вернет ассоциативный массив или FALSE, если записей не найдено
        // попытка обратиться к FALSE по ключу вернет null
        if ($query->fetch()['problem_id'] === null)
        {
            throw new Exception('problem_id ' . ($problem_id === null ? 'NULL' : $problem_id) . ' not found in \'problem\'; Method: ' . __METHOD__ . '; line: ' . __LINE__);
        }


        // проверка факта выдачи задания пользователю
        if (!self::isAssigned($user_id, $problem_id))
        {
            throw new UserException(PROBLEM_NOT_ASSIGNED, PROBLEM_NOT_ASSIGNED_MSG, 200);
        }
        else
        {
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


            // проверка того, что пользователсь прислал правильный ответ (заодно поиск assignment_id для следующего шага)
            $query = $conn->prepare('SELECT assignment.assignment_id, assignment.correct_answer_provided 
                                     FROM assignment
                                     WHERE assignment.problem_id = ? AND assignment.user_id = ?');
            $query->execute(array($problem_id, $user_id));
            $assignment = $query->fetch()['assignment_id'];
            $data['correct_answer_provided'] = (bool)$query->fetch()['correct_answer_provided'];


            // подсчет количества присланных ответов
            $query = $conn->prepare('SELECT COUNT(*) FROM answer
                                     WHERE answer.assignment_id = ?');
            $query->execute(array($assignment));
            $data['answers_provided'] = $query->fetchColumn();
        }

        unset($conn);

        return $data;
    }




    public static function getAssignments ($user_id)
    {
        // TODO: описать метод в документации
        // TODO: добавить код ошибки для случая, когда пользователь запросил больше заданий, чем можно
    }

}
?>