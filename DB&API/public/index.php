<?php
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

require '../vendor/autoload.php';
require '../config/config.php';

$app = new \Slim\App();

$container = $app->getContainer();

$container['logger'] = function ($c)
{
    $logger = new \Monolog\Logger('APIlogger');
    $file_handler = new \Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['errorHandler'] = function ($c)
{
    return function ($request, $response, $exception) use ($c) {
        // error caused by user's actions
        if ($exception instanceof UserExceptions)
        {
            return $c['response']->withJson(array ('success' => 'false',
                'error' => ["code" => $exception->getCode(),
                    "message" => $exception->getMessage()]), 200, JSON_UNESCAPED_UNICODE);
        }
        // otherwise, system error
        $c->logger->addInfo($exception->getMessage());
        return $c['response']->withStatus($exception->getCode())
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};

$app->get('/problems/problem', function (Request $request, Response $response)
{
    $type = $request->getQueryParam('type', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);

    if ($type === null)
    {
        throw new Exception("Invalid parameter: type is NULL; index file, line: " . __LINE__, 404);
    }
    if ($user_id === null)
    {
        throw new Exception("Invalid parameter: user_id is NULL; index file, line: " . __LINE__, 404);
    }
    if ($service === null)
    {
        throw new Exception("Invalid parameter: service is NULL; index file, line: " . __LINE__, 404);
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
    $response = $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

$app->get('/problems/solution', function (Request $request, Response $response)
{
    $problem_id = $request->getQueryParam('problem_id', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);

    if ($problem_id === null)
    {
        throw new Exception("Invalid parameter: problem_id is NULL; index file, line: " . __LINE__, 404);
    }

    if ($user_id === null)
    {
        throw new Exception("Invalid parameter: user_id is NULL; index file, line: " . __LINE__, 404);
    }

    if ($service === null)
    {
        throw new Exception("Invalid parameter: service is NULL; index file, line: " . __LINE__, 404);
    }

    $user_id = dbMisc::getGlobalUserId($user_id, $service);

    $data = dbResult::getSolution($user_id, $problem_id);
    $answer = array ('success' => 'true' , 'data' => $data);
    $response = $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

$app->get('/problems/answer', function (Request $request, Response $response)
{
    $problem_id = $request->getQueryParam('problem_id', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);

    if ($problem_id === null)
    {
        throw new Exception("Invalid parameter: problem_id is NULL; index file, line: " . __LINE__, 404);
    }

    if ($user_id === null)
    {
        throw new Exception("Invalid parameter: user_id is NULL; index file, line: " . __LINE__, 404);
    }

    if ($service === null)
    {
        throw new Exception("Invalid parameter: service is NULL; index file, line: " . __LINE__, 404);
    }

    $user_id = dbMisc::getGlobalUserId($user_id, $service);

    $data = dbResult::getAnswer($user_id, $problem_id);
    $answer = array ('success' => 'true' , 'data' => $data);
    $response = $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

$app->post('problems/answer', function (Request $request, Response $response)
{   
    $problem_id = $request->getParam('problem_id', null);
    $answer = $request->getParam('answer', null);
    $user_id = $request->getParam('user_id', null);
    $service = $request->getParam('service', null);

    if ($problem_id === null) 
    {
        throw new Exception("Invalid parameter: problem_id is NULL; index file, line: " . __LINE__, 404);
    }

    if ($user_answer === null) 
    {
        throw new Exception("Invalid parameter: user_answer is NULL; index file, line: " . __LINE__, 404);
    }

    if ($user_id === null) 
    {
        throw new Exception("Invalid parameter: user_id is NULL; index file, line: " . __LINE__, 404);
    }

    if ($service === null) 
    {
        throw new Exception("Invalid parameter: service is NULL; index file, line: " . __LINE__, 404);
    }
    $user_id = dbMisc::getGlobalUserId($user_id, $service);
    if (checkAnswer::checkB(dbAssignment::assignAnswer($user_id, $problem_id, $user_answer), $user_answer)) 
    {
        $answer = array ('success' => 'true' , 'result' => true);
    } 
    else 
    {
        $answer = array ('success' => 'true' , 'result' => false);
    }
    $response = $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

$app->get('/resources/resource', function (Request $request, Response $response)
{   
    $data = dbResource::getResourceTypes();
    $answer = array ('success' => 'true' , 'data' => $data);
    $response = $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

$app->put('/resources/resource', function (Request $request, Response $response)
{   
    $user_id = $request->getParam('user_id', null);
    $service = $request->getParam('service', null);
    $resource_type = urldecode($request->getParam('resource_type', null));
    // нужно ли это??
    $resource_type = mb_convert_encoding($resource_type, "utf-8", mb_detect_encoding($resource_type));

    if ($user_id === null) {
        throw new Exception("Invalid parameter: user_id is NULL; index file, line: " . __LINE__, 404);
    }

    if ($service === null) {
        throw new Exception("Invalid parameter: service is NULL; index file, line: " . __LINE__, 404);
    }

    if ($resource_type === null) {
        throw new Exception("Invalid parameter: resource_type is NULL; index file, line: " . __LINE__, 404);
    }
    $user_id = dbMisc::getGlobalUserId($user_id, $service);
    dbResource::setPreferredResource($user_id, $resource_type);
    
    $answer = array ('success' => 'true');
    $response = $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

$app->get('/problem_types/problem_type', function (Request $request, Response $response)
{   
    $data = dbMisc::getProblemTypes();
    $answer = array ('success' => 'true' , 'data' => $data);
    $response = $response->withJson($answer, 200, JSON_UNESCAPED_UNICODE);
    return $response;
});

$app->run();
?>
