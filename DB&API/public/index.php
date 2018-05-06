<?php
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

require '../vendor/autoload.php';
require '../config/config.php';

$app = new \Slim\App();

$container = $app->getContainer();

// логгер
$container['logger'] = function ($c)
{
    $logger = new \Monolog\Logger('APIlogger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

// обработка ошибок
$container['errorHandler'] = function ($c)
{
    return function ($request, $response, $exception) use ($c) {
        // если ошибка произошла по вине пользователя
        if ($exception instanceof UserExceptions)
        {
            return $c['response']->withJson(array ('success' => 'false',
                'error' => ["code" => $exception->getCode(),
                    "message" => $exception->getMessage()]), 200, JSON_UNESCAPED_UNICODE);
        }
        // иначе, системная ошибка
        $c->logger->addInfo($exception->getMessage());
        return $c['response']->withStatus($exception->getCode())
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};

// Виталик
$app->get('/problems/problem', function (Request $request, Response $response)
{
    $type = $request->getQueryParam('type', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);

    if ($type === null)
    {
        throw new Exception("Invalid parameter: type is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }
    if ($user_id === null)
    {
        throw new Exception("Invalid parameter: user_id is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }
    if ($service === null)
    {
        throw new Exception("Invalid parameter: service is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }

    $user_id = dbMisc::getGlobalUserId($user_id, $service);

    if (is_numeric($type))
    {
        $data = dbProblem::getProblemByNumber($user_id, $type);
    }
    elseif ($type === 'random')
    {
        $data = dbProblem::getProblem($user_id);
    }
    else
    {
        $data = dbProblem::getProblemByType($user_id, $type);
    }

    $problem = $data['problem'];
    unset ($data["problem"]);
    $answer = array ('success' => 'true' , 'problem' => $problem,'data' => $data);
    $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

// Виталик
$app->get('/problems/solution', function (Request $request, Response $response)
{
    $problem_id = $request->getQueryParam('problem_id', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);

    if ($problem_id === null)
    {
        throw new Exception("Invalid parameter: problem_id is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }

    if ($user_id === null)
    {
        throw new Exception("Invalid parameter: user_id is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }

    if ($service === null)
    {
        throw new Exception("Invalid parameter: service is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }

    $user_id = dbMisc::getGlobalUserId($user_id, $service);

    $data = dbResult::getSolution($user_id, $problem_id);
    $answer = array ('success' => 'true' , 'data' => $data);
    $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

// Виталик
$app->get('/problems/answer', function (Request $request, Response $response)
{
    $problem_id = $request->getQueryParam('problem_id', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);

    if ($problem_id === null)
    {
        throw new Exception("Invalid parameter: problem_id is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }

    if ($user_id === null)
    {
        throw new Exception("Invalid parameter: user_id is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }

    if ($service === null)
    {
        throw new Exception("Invalid parameter: service is NULL; Method: " . __METHOD__ . "; line: " . __LINE__, 404);
    }

    $user_id = dbMisc::getGlobalUserId($user_id, $service);

    $data = dbResult::getAnswer($user_id, $problem_id);
    $answer = array ('success' => 'true' , 'data' => $data);
    $response->getBody()->write($response->withJson($answer, 200, JSON_UNESCAPED_UNICODE));
    return $response;
});

// Андрей
$app->post('problems/answer', function (Request $request, Response $response)
{

});

// Андрей
$app->get('/resources/resource', function (Request $request, Response $response)
{

});

// Андрей
$app->put('/resources/resource', function (Request $request, Response $response)
{

});

// Андрей
$app->get('/problem_types/problem_type', function (Request $request, Response $response)
{

});

$app->run();
?>
