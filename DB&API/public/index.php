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

// обработчик пользовательских исключительных ситуаций, возникающих в модулях работы с БД
// например: кончились задания для выдачи пользователю, тема заданий не обнаружена...
$container['errorHandler'] = function ()
{
    return function ($request, $response, $exception) {
        if ($exception instanceof UserException)
        {
            return $response->withJson(array(
                'status' => $exception->jsonStatus,
                'data' => [
                    'message' => $exception->getMessage()
                ]), $exception->getCode(), JSON_UNESCAPED_UNICODE);
        }
        else
        {
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

// 404 - указанный ресурс не найден
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

// 405 - данный ресурс не поддерживает указанное действие
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

// 500 - внутренняя ошибка сервера
// переопределение существующего обработчика Slim
$container['phpErrorHandler'] = function ($c)
{
    return function ($request, $response, $error) use ($c)
    {
        $c->logger->addInfo($error->getMessage());
        return $response->withJson(array(
            'status' => ERROR,
            'data' => [
                'message' => 'Server error occurred, please try again later!'
            ]), 500, JSON_UNESCAPED_UNICODE);
    };
};



// запрос задания
$app->post('/assignments', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

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
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($requestBody['user']);
    if (!ctype_digit($user))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $handler($response, WRONG_SERVICE, 400);
    }
    unset ($requestBody['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }



    /*
     * ОБРАБОТКА ЗАПРОСА
     */


    return $response->write('boob');
});

// запрос статистики по заданию
$app->get('/assignments', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    $problem = $request->getQueryParam('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($queryParams['user']);
    if (!ctype_digit($user))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // валидация параметра problem
    if ($problem === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'problem'),
            400);
    }
    unset ($queryParams['problem']);
    if (!ctype_digit($problem))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'problem', 'non-negative integer'),
            400);
    }

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $handler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);


    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }



    /*
     * ОБРАБОТКА ЗАПРОСА
     */
});

// запрос количества (выданных/решенных/нерешенных) заданий
$app->get('/assignments/count', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);

    // валидация параметра user
    if ($user === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($queryParams['user']);
    if (!ctype_digit($user))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $handler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }



    /*
     * ОБРАБОТКА ЗАПРОСА
     */

});

// проверка ответа пользователя
$app->post('/assignments/answer', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

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
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($requestBody['user']);
    if (!ctype_digit($user))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // валидация параметра problem
    if ($problem === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'problem'),
            400);
    }
    unset ($requestBody['problem']);
    if (!ctype_digit($problem))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'problem', 'non-negative integer'),
            400);
    }

    // валидация параметра answer
    if ($answer === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'answer'),
            400);
    }
    unset ($requestBody['answer']);

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $handler($response, WRONG_SERVICE, 400);
    }
    unset ($requestBody['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

});

// запрос правильного ответа задания
$app->get('/problems/{problem}/answer', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    $problem = $request->getAttribute('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($queryParams['user']);
    if (!ctype_digit($user))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // валидация атрибута запроса problem
    if (!ctype_digit($problem))
    {
        return $handler($response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $handler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

});

// запрос разбора задания
$app->get('/problems/{problem}/solution', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    $problem = $request->getAttribute('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($queryParams['user']);
    if (!ctype_digit($user))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // валидация атрибута запроса problem
    if (!ctype_digit($problem))
    {
        return $handler($response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $handler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

});

// запрос условия задания
$app->get('/problems/{problem}/statement', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // обязательные параметры
    $user = $request->getQueryParam('user', null);
    $service = $request->getQueryParam('service', null);
    $problem = $request->getAttribute('problem', null);

    // валидация параметра user
    if ($user === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($queryParams['user']);
    if (!ctype_digit($user))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // валидация атрибута запроса problem
    if (!ctype_digit($problem))
    {
        return $handler($response,
            sprintf(WRONG_QUERY_ATTRIBUTE_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $handler($response, WRONG_SERVICE, 400);
    }
    unset ($queryParams['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }

    /*
     * ОБРАБОТКА ЗАПРОСА
     */

});

// запрос доступных типов ресурсов
$app->get('/resources', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // если тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }



    /*
     * ОБРАБОТКА ЗАПРОСА
     */
});

// установление предпочитаемого типа ресурса
$app->put('/resources', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

    // тело запроса (все параметры)
    $requestBody = $request->getParsedBody();

    // обязательные параметры
    $user = $request->getParam('user', null);
    $service = $request->getParam('service', null);
    $resource_type = $request->getParam('resource_type', null);

    // валидация параметра user
    if ($user === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'user'),
            400);
    }
    unset ($requestBody['user']);
    if (!ctype_digit($user))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'user', 'non-negative integer'),
            400);
    }

    // валидация парамтра resource_type
    if ($resource_type === null)
    {
        return $handler($response,
            sprintf(PARAMETER_REQUIRED, 'resource_type'),
            400);
    }
    unset ($requestBody['resource_type']);
    if (!ctype_alpha($resource_type))
    {
        return $handler($response,
            sprintf(WRONG_PARAMETER_TYPE, 'resource', 'alphabetical characters only string'),
            400);
    }

    // сервис не поддерживается
    if (in_array($service, array_keys(SERVICES)) === false)
    {
        return $handler($response, WRONG_SERVICE, 400);
    }
    unset ($requestBody['service']);

    // если после извлечения всех параметров тело непустое, то оно содержит неизвестные параметры
    if (count($requestBody) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($requestBody))),
            400);
    }



    /*
     * ОБРАБОТКА ЗАПРОСА
     */
});

// запрос доступных тем заданий
$app->get('/problem_types', function (Request $request, Response $response)
{
    /*
     * ВАЛИДАЦИЯ ЗАПРОСА
     */

    // экземпляр обработчика ошибок запроса
    $handler = $this->get('requestErrorHandler');

    // параметры запроса
    $queryParams = $request->getQueryParams();

    // если тело непустое, то оно содержит неизвестные параметры
    if (count($queryParams) > 0)
    {
        return $handler($response,
            sprintf(UNPROCESSABLE_PARAMETER, implode(', ', array_keys($queryParams))),
            400);
    }



    /*
     * ОБРАБОТКА ЗАПРОСА
     */
});

$app->run();
?>