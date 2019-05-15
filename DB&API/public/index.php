<?php
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

require '../vendor/autoload.php';
include '../config/config.php';

$app = new \Slim\App();

$container = $app->getContainer();

$container['logger'] = function ()
{
    $logger = new \Monolog\Logger('APIlogger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

// обработчик ошибок и исключений, возникающих НЕ в данном файле, а в модулях работы с БД:
// исключения предметной области (указанное задание не было выдано, указанный тип ресурса не существует...
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
                ]), $exception->getCode(), JSON_UNESCAPED_UNICODE);
        }
        else
        {
            // TODO: проверить, что сообщение действительно заносится в лог
            $c->logger->addInfo($exception->getMessage() . ' ' . ' ' . $exception->getFile() . ' ' . $exception->getLine());
            return $response->withJson(array(
                'status' => ERROR,
                'data' => [
                    'message' => 'Server error occurred, please try again later!'
                ]), 500, JSON_UNESCAPED_UNICODE);
        }
    };
};

// обработчик ошибок, возникающих при неправильно составленном запросе
$container['requestErrorHandler'] = function ()
{
    return function ($response, $message, $httpStatus, $jsonStatus = ERROR)
    {
        return $response->withJson(array(
            'status' => $jsonStatus,
            'data' => [
                'message' => $message
            ]), $httpStatus, JSON_UNESCAPED_UNICODE);
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
            ]), 404, JSON_UNESCAPED_UNICODE);
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
                ]), 405, JSON_UNESCAPED_UNICODE);
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
            ]), 500, JSON_UNESCAPED_UNICODE);
    };
};



/*
 * запрос нового задания
 */
$app->post('/assignments', function (Request $request, Response $response)
{
    // ВАЛИДАЦИЯ ЗАПРОСА

    // экземпляр обработчика ошибок запроса
    $errorHandler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getParam('user', null);
    $service = $request->getParam('service', null);
    // необязательный параметр
    $filter = $request->getParam('filter', null);
    unset ($requestBody['filter']);

    // валидация параметра user
    if ($user === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset ($requestBody['user']);

    // валидация параметра service
    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $errorHandler($response, WRONG_SERVICE, 400);
    }
    unset ($requestBody['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }



    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    // глобальный id пользователя
    $guid = dbMisc::getGlobalUserId($user, $service);

    // получение еще не выданного задания
    $problem = dbAssignment::getUnassignedProblem($guid, $filter);

    //
    dbAssignment::assignProblem($guid, $problem);



    return $response->write('boob');
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
    $errorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    $problem = $request->getQueryParam('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($queryParams['user']);
    if (!ctype_digit($user))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // валидация параметра problem
    if ($problem === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'problem'),
            400);
    }
    unset ($queryParams['problem']);
    if (!ctype_digit($problem))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'problem', 'non-negative integer'),
            400);
    }

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $errorHandler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);


    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }



    /*
     * ОБРАБОТКА ЗАПРОСА
     */
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
    $errorHandler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getParam('user', null);
    $service = $request->getParam('service', null);
    $problem = $request->getParam('problem', null);
    $answer = $request->getParam('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($requestBody['user']);
    if (!ctype_digit($user))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // валидация параметра problem
    if ($problem === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'problem'),
            400);
    }
    unset ($requestBody['problem']);
    if (!ctype_digit($problem))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'problem', 'non-negative integer'),
            400);
    }

    // валидация параметра answer
    if ($answer === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'answer'),
            400);
    }
    unset ($requestBody['answer']);

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $errorHandler($response, WRONG_SERVICE, 400);
    }
    unset ($requestBody['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

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
    $errorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    $problem = $request->getAttribute('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset ($queryParams['user']);

    // валидация атрибута запроса problem
    if (!ctype_digit($problem))
    {
        return $errorHandler($response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset($queryParams['user']);

    // валидация сервиса
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $errorHandler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $user = dbMisc::getGlobalUserId($user, $service);

    $problem = $problem ^ $user;

    if (!dbAssignment::isAssigned($user, $problem))
    {
        throw new APIException(PROBLEM_NOT_ASSIGNED, PROBLEM_NOT_ASSIGNED_MSG, 200);
    }
    else
    {

    }

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
    $errorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    $problem = $request->getAttribute('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset ($queryParams['user']);

    // валидация атрибута запроса problem
    if (!ctype_digit($problem))
    {
        return $errorHandler($response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset ($queryParams['problem']);

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $errorHandler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

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
    $errorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    $problem = $request->getAttribute('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset ($queryParams['user']);

    // валидация атрибута запроса problem
    if (!ctype_digit($problem))
    {
        return $errorHandler($response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset ($queryParams['problem']);

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $errorHandler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

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
    $errorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // если тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $answer = [
        'status' => 0,
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
    $errorHandler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // если тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $answer = [
        'status' => 0,
        'data' => dbResource::getResourceTypes()
    ];

    return $response->withJson($answer, 200);
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
    $errorHandler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getAttribute('route')->getArgument('user');
    $service = $request->getParam('service', null);
    $resource_type = $request->getParam('resource_type', null);

    // валидация параметра user
    if ($user === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset ($requestBody['user']);


    // валидация парамтра resource_type
    if ($resource_type === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'resource_type'),
            400);
    }
    if (!ctype_alpha($resource_type))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'resource', 'alphabetical characters only string'),
            400);
    }
    unset ($requestBody['resource_type']);


    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $errorHandler($response, WRONG_SERVICE, 400);
    }
    unset ($requestBody['service']);


    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $uid = dbMisc::getGlobalUserId($user, $service);
    return $response->withJson([
        'status' => 0,
        'data' =>
            [
                'user' => $user,
                'year' => dbResource::setPreferredResource($)
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
    $errorHandler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getAttribute('route')->getArgument('user');
    $service = $request->getParam('service', null);
    $year = $request->getParam('year', null);


    // валидация параметра user
    if ($user === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    if (!ctype_digit($user))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }
    unset ($requestBody['user']);


    // валидация парамтра year
    if ($year === null)
    {
        return $errorHandler($response,
            sprintf(PARAMETER_REQUIRED, 'year'),
            400);
    }
    if (!ctype_digit($year))
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'year', 'number above ' . MIN_YEAR),
            400);
    }
    if ($year < MIN_YEAR)
    {
        return $errorHandler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'year', 'number above ' . MIN_YEAR),
            422);
    }
    unset ($requestBody['year']);


    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $errorHandler($response, WRONG_SERVICE, 400);
    }
    unset ($requestBody['service']);


    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $errorHandler($response,
            sprintf(UNKNOWN_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }


    /*
     * ОБРАБОТКА ЗАПРОСА
     */

    $uid = dbMisc::getGlobalUserId($user, $service);
    return $response->withJson([
        'status' => 0,
        'data' =>
        [
            'user' => $user,
            'year' => dbMisc::setYearRange($uid, $year)
        ]
    ], 200);
});

$app->run();
?>