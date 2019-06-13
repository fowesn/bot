<?php
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

require '../vendor/autoload.php';
include '../config/config.php';

$app = new \Slim\App();

$container = $app->getContainer();

$container['logger'] = function()
{
    $logger = new \Monolog\Logger('APIlogger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

// обработчик ошибок и исключений, возникающих НЕ в данном файле, а в модулях работы с БД:
// исключения предметной области (задания кончились, указанный тип ресурса не существует...
// системные ошибки
// все исключения, возникающие в модулях работы с БД, отлавливаются здесь
$container['errorHandler'] = function ($c)
{
    return function ($request, $response, $exception) use ($c) {
        if ($exception instanceof APIException)
        {
            return $response->withJson(array(
                'status' => $exception->jsonStatus,
                'data' => [
                    'message' => $exception->getMessage()
                ]), $exception->getCode());
        }
        else
        {
            $c->logger->addInfo($exception->getMessage() . ' ' . ' ' . $exception->getFile() . ' ' . $exception->getLine());
            return $response->withJson(array(
                'status' => ERROR,
                'data' => [
                    'message' => 'Server error occurred, please try again later!'
                ]), 500);
        }
    };
};

// обработчик ошибок, возникающих в данном файле
$container['requestErrorHandler'] = function ()
{
    return function ($response, $message, $httpStatus, $jsonStatus = ERROR)
    {
        return $response->withJson(array(
            'status' => $jsonStatus,
            'data' => [
                'message' => $message
            ]), $httpStatus);
    };
};

// обработчик 404 - указанный ресурс не найден
// переопределение существующего обработчика Slim
$container['notFoundHandler'] = function ()
{

    return function ($request, $response)
    {
        return $response->withJson(array(
            'status' => ERROR,
            'data' => [
                'message' => 'URI not found!'
            ]), 404);
    };
};

// обработчик 405 - данный ресурс не поддерживает указанное действие
// переопределение существующего обработчика Slim
$container['notAllowedHandler'] = function ()
{
    return function ($request, $response, $methods) {

        return $response->withHeader('Allow', implode(', ', $methods))
            ->withJson(array(
                'status' => ERROR,
                'data' =>[
                    'message'=> 'Method must be one of: ' . implode(', ', $methods) . '!',
                ]), 405);
    };
};

// обработчик 500 - внутренняя ошибка сервера
// переопределение существующего обработчика Slim
$container['phpErrorHandler'] = function ($c)
{
    return function ($request, $response, $error) use ($c)
    {
        $c->logger->addInfo($error->getMessage() . ' ' . $error->getFile() . ' ' . $error->getLine());
        return $response->withJson(array(
            'status' => ERROR,
            'data' => [
                'message' => 'Server error occurred, please try again later!'
            ]), 500);
    };
};




/*
 * запрос нового задания
 */
$app->post('/assignments', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getParam('user', null);
    $service = $request->getParam('service', null);
    // необязательный параметр
    $filter = $request->getParam('filter', null);
    unset($requestBody['filter']);

    // валидация параметра user
    if ($user === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset($requestBody['user']);

    // валидация параметра service
    if ($service === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'service'),
            400);
    }
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_ENUM_PARAMETER, 'service', implode(', ', array_keys(SERVICES))),
            400);
    }
    unset($requestBody['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    // глобальный id пользователя
    $user_id = dbUser::getGlobalUserId($user, $service);

    // получение еще не выданного задания
    $problem_id = dbAssignment::getUnassignedProblem($user_id, $filter);

    // назначение задания пользователю
    dbAssignment::assignProblem($user_id, $problem_id);

    $data['problem'] = $problem_id + $user_id;
    $data = array_merge($data, dbProblem::getProblemData($problem_id));
    $data['resources'] = dbResource::getPreferredResource($user_id, dbProblem::getStatement($problem_id));

    return $response->withJson([
        'status' => SUCCESS,
        'data' => $data
    ], 201);
});


/*
 * запрос статистики (одного/всех) заданий/нерешенных заданий
 */
$app->get('/assignments', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    // необязательные параметры
    $problem = $request->getQueryParam('problem', null);
    $filter = $request->getQueryParam('filter', null);

    // валидация параметра user
    if ($user === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset($queryParams['user']);

    // валидация параметра problem
    if ($problem !== null)
    {
        if (!ctype_digit($problem))
        {
            return $requestErrorHandler(
                $response,
                sprintf(WRONG_PARAMETER_TYPE, 'problem', 'non-negative integer'),
                400);
        }
        unset($queryParams['problem']);

        // нельзя указать два необязательных параметра одновременно
        if ($filter !== null)
        {
            return $requestErrorHandler(
                $response,
                sprintf(UNKNOWN_PARAMETER, 'filter'),
                400);
        }
    }
    unset($queryParams['filter']);

    // валидация параметра service
    if ($service === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'service'),
            400);
    }
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_ENUM_PARAMETER, 'service', implode(', ', array_keys(SERVICES))),
            400);
    }
    unset($queryParams['service']);


    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $user_id = dbUser::getGlobalUserId($user, $service);

    // статистика по заданию
    if ($problem !== null)
    {
        $problem_id = $problem - $user_id;

        if (!dbAssignment::isAssigned($user_id, $problem_id))
        {
            return $requestErrorHandler(
                $response,
                PROBLEM_NOT_ASSIGNED_MSG,
                200,
                PROBLEM_NOT_ASSIGNED);
        }
        else
        {
            $data = dbAssignment::getAssignmentData(dbAssignment::getAssignmentId($user_id, $problem_id));
            $data = array_merge($data, dbProblem::getProblemData($problem_id));
        }
    }
    // статистика по фильтру
    elseif ($filter !== null)
    {
        switch ($filter)
        {
            case "нерешенные":
                $data = dbAssignment::getUnsolvedProblems($user_id);
                foreach ($data as &$problem) {
                    $problem += $user_id;
                }
                break;
            default:
                return $requestErrorHandler(
                    $response,
                    sprintf(WRONG_ENUM_PARAMETER, 'filter', implode(', ', ASSIGNMENT_FILTERS)),
                    422,
                    UNKNOWN_FILTER);
        }
    }
    // статистика по всем заданиям
    else
    {
        $data = dbAssignment::getAssignmentsCount($user_id);
    }

    return $response->withJson([
        'status' => SUCCESS,
        'data' => $data
    ], 200);
});


/*
 * проверка ответа
 */
$app->post('/assignments/answers', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getParam('user', null);
    $service = $request->getParam('service', null);
    $problem = $request->getParam('problem', null);
    $user_answer = $request->getParam('answer', null);

    // валидация параметра user
    if ($user === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset($requestBody['user']);

    // валидация параметра problem
    if ($problem === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'problem'),
            400);
    }
    if (!ctype_digit($problem))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'problem', 'non-negative integer'),
            400);
    }
    unset($requestBody['problem']);

    // валидация параметра answer
    if ($user_answer === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'answer'),
            400);
    }
    unset ($requestBody['answer']);

    // валидация параметра service
    if ($service === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'service'),
            400);
    }
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_ENUM_PARAMETER, 'service', implode(', ', array_keys(SERVICES))),
            400);
    }
    unset($requestBody['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $user_id = dbUser::getGlobalUserId($user, $service);

    $problem_id = $problem - $user_id;

    if (!dbAssignment::isAssigned($user_id, $problem_id))
    {
        return $requestErrorHandler(
            $response,
            PROBLEM_NOT_ASSIGNED_MSG,
            200,
            PROBLEM_NOT_ASSIGNED);
    }
    else
    {
        $isAnswerCorrect = checkShortAnswer::checkAnswer($problem_id, $user_answer);
        dbAssignment::updateAssignment(
            dbAssignment::getAssignmentId($user_id, $problem_id),
            $isAnswerCorrect,
            $user_answer);
    }

    return $response->withJson([
        'status' => SUCCESS,
        'data' => [
            'answer_correct' => $isAnswerCorrect
        ],
    ], 201);
});


/*
 * запрос условия задания
 */
$app->get('/problems/{problem}/statement', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $problem = $request->getAttribute('route')->getArgument('problem');
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);

    // валидация параметра user
    if ($user === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset($queryParams['user']);

    // валидация атрибута запроса problem
    if ($problem === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'problem'),
            400);
    }
    if (!ctype_digit($problem))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'problem', 'non-negative integer'),
            400);
    }
    unset($queryParams['problem']);

    // валидация параметра service
    if ($service === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'service'),
            400);
    }
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_ENUM_PARAMETER, 'service', implode(', ', array_keys(SERVICES))),
            400);
    }
    unset($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $user_id = dbUser::getGlobalUserId($user, $service);

    $problem_id = $problem - $user_id;

    if (!dbAssignment::isAssigned($user_id, $problem_id))
    {
        return $requestErrorHandler(
            $response,
            PROBLEM_NOT_ASSIGNED_MSG,
            200,
            PROBLEM_NOT_ASSIGNED);
    }
    else
    {
        $data = dbProblem::getProblemData($problem_id);
        $data['problem'] = $problem;
        $data['resources'] = dbResource::getPreferredResource($user_id, dbProblem::getStatement($problem_id));
    }

    return $response->withJson([
        'status' => SUCCESS,
        'data' => $data
    ], 200);
});


/*
 * запрос разбора задания
 */
$app->get('/problems/{problem}/solution', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $problem = $request->getAttribute('route')->getArgument('problem');
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);

    // валидация параметра user
    if ($user === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset($queryParams['user']);

    // валидация атрибута запроса problem
    if ($problem === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'problem'),
            400);
    }
    if (!ctype_digit($problem))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'problem', 'non-negative integer'),
            400);
    }
    unset($queryParams['problem']);

    // валидация параметра service
    if ($service === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'service'),
            400);
    }
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_ENUM_PARAMETER, 'service', implode(', ', array_keys(SERVICES))),
            400);
    }
    unset($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $user_id = dbUser::getGlobalUserId($user, $service);

    $problem_id = $problem - $user_id;

    if (!dbAssignment::isAssigned($user_id, $problem_id))
    {
        return $requestErrorHandler(
            $response,
            PROBLEM_NOT_ASSIGNED_MSG,
            200,
            PROBLEM_NOT_ASSIGNED);
    }
    else
    {
        $data['resources'] = dbResource::getPreferredResource($user_id, dbProblem::getSolution($problem_id));
    }

    return $response->withJson([
        'status' => SUCCESS,
        'data' => $data
    ], 200);
});


/*
 * запрос правильного ответа
 */
$app->get('/problems/{problem}/answer', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $problem = $request->getAttribute('route')->getArgument('problem');
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);

    // валидация параметра user
    if ($user === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset($queryParams['user']);

    // валидация атрибута запроса problem
    if ($problem === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'problem'),
            400);
    }
    if (!ctype_digit($problem))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'problem', 'non-negative integer'),
            400);
    }
    unset($queryParams['problem']);

    // валидация параметра service
    if ($service === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'service'),
            400);
    }
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_ENUM_PARAMETER, 'service', implode(', ', array_keys(SERVICES))),
            400);
    }
    unset($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $user_id = dbUser::getGlobalUserId($user, $service);

    $problem_id = $problem - $user_id;

    if (!dbAssignment::isAssigned($user_id, $problem_id))
    {
        return $requestErrorHandler(
            $response,
            PROBLEM_NOT_ASSIGNED_MSG,
            200,
            PROBLEM_NOT_ASSIGNED);
    }
    else
    {
        $data['answer'] = dbProblem::getAnswer($problem_id);
        dbAssignment::updateAssignment(
            dbAssignment::getAssignmentId($user_id, $problem_id),
            ANSWER_REQUEST);
    }

    return $response->withJson([
        'status' => SUCCESS,
        'data' => $data
    ], 200);
});


/*
 * запрос доступных тем заданий
 */
$app->get('/problems/problem_types', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // если тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $answer = [
        'status' => SUCCESS,
        'data' => dbProblem::getProblemTypes()
    ];

    return $response->withJson($answer, 200);
});


/*
 * запрос доступных типов ресурсов
 */
$app->get('/resources', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // если тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    return $response->withJson([
        'status' => SUCCESS,
        'data' => dbResource::getResourceTypes()
    ], 200);
});


/*
 * установление предпочитаемого типа ресурса
 */
$app->put('/users/{user}/resource', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getAttribute('route')->getArgument('user');
    $service = $request->getParam('service', null);
    $resource_type = $request->getParam('resource_type', null);

    // валидация параметра user
    if ($user === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'user', 'non-negative integer'),
            400);
    }


    // валидация парамтра resource_type
    if ($resource_type === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'resource_type'),
            400);
    }
    if (!preg_match('/^[а-яёa-z]+$/msiu', $resource_type))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'resource_type', 'alphabetical characters only string'),
            400);
    }
    unset($requestBody['resource_type']);


    // валидация параметра service
    if ($service === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'service'),
            400);
    }
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_ENUM_PARAMETER, 'service', implode(', ', array_keys(SERVICES))),
            400);
    }
    unset($requestBody['service']);


    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $user_id = dbUser::getGlobalUserId($user, $service);
    return $response->withJson([
        'status' => SUCCESS,
        'data' =>
            [
                'user' => $user,
                'resource_type' => dbResource::setPreferredResource($user_id, $resource_type)
            ]
    ], 200);
});


/*
 * установление предпочитаемого года
 */
$app->put('/users/{user}/year', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $requestErrorHandler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getAttribute('route')->getArgument('user');
    $service = $request->getParam('service', null);
    $year = $request->getParam('year', null);

    // валидация параметра user
    if ($user === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'user', 'non-negative integer'),
            400);
    }


    // валидация парамтра year
    if ($year === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'year'),
            400);
    }
    if (!ctype_digit($year))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'year', 'number equal to or above ' . MIN_YEAR . ' and equal to or below ' . date('Y')),
            400);
    }
    if ($year < MIN_YEAR)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'year', 'number equal to or above ' . MIN_YEAR),
            422,
            YEAR_BELOW_MIN);
    }
    if ($year > date('Y'))
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_PARAMETER_TYPE, 'year', 'number equal to or below ' . date('Y')),
            422,
            YEAR_ABOVE_MAX);
    }
    unset($requestBody['year']);


    // валидация параметра service
    if ($service === null)
    {
        return $requestErrorHandler(
            $response,
            sprintf(PARAMETER_REQUIRED, 'service'),
            400);
    }
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $requestErrorHandler(
            $response,
            sprintf(WRONG_ENUM_PARAMETER, 'service', implode(', ', array_keys(SERVICES))),
            400);
    }
    unset($requestBody['service']);


    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $requestErrorHandler(
            $response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $user_id = dbUser::getGlobalUserId($user, $service);
    return $response->withJson([
        'status' => SUCCESS,
        'data' =>
        [
            'user' => $user,
            'year' => dbUser::setYearRange($user_id, $year)
        ]
    ], 200);
});



$app->run();
?>