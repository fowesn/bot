<?php
<<<<<<< HEAD
use \Psr\http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App();

=======
//use \Psr\http\Message\ServerRequestInterface as Request;
//use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

require '../vendor/autoload.php';
require '../setting.php';

$app = new \Slim\App();

$app->get('/api/v1/problems/problem', function (Request $request, Response $response) {

    /** @var  mixed type of problem requested */
    $type = $request->getQueryParam('type', null);
    /** @var  integer id of the user who requested problem */
    $user_id = $request->getQueryParam('user_id', null);
    /** @var  string service used by the user :) (telegram, vk.com ...) */
    $service = $request->getQueryParam('service', null);


    return $response;
});


$app->get('/api/v1/solutions/solution', function (Request $request, Response $response) {

    $problem_id = $request->getQueryParam('problem_id', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);


    return $response;
});

$app->get('/api/v1/answers/answer', function (Request $request, Response $response) {

    $problem_id = $request->getQueryParam('problem_id', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);


    return $response;
});

$app->post('/api/v1/answers/answer', function (Request $request, Response $response) {

    $problem_id = $request->getQueryParam('problem_id', null);
    $user_answer = $request->getQueryParam('user_answer', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);


    return $response;
});

$app->get('/api/v1/resources/resource', function (Request $request, Response $response) {

    $data = dbResource::getResourceTypes();
    $answer = array ('success' => 'true' , 'data' => $data);
    $response->getBody()->write($response->withJson($answer, 200, JSON_UNESCAPED_UNICODE));
    return $response;

});

$app->put('api/v1/resources/resource', function (Request $request, Response $response)
{

});

$app->get('/api/v1/problems/problem_types/problem_type', function (Request $request, Response $response) {

    $data = dbMisc::getProblemTypes();
    $answer = array ('success' => 'true' , 'data' => $data);
    $response->getBody()->write($response->withJson($answer, 200, JSON_UNESCAPED_UNICODE));
    return $response;

});

$app->get('/api/v1/test', function (Request $request, Response $response) {
    $user_id = $request->getQueryParam('user_id', null);
    $problem_id = $request->getQueryParam('problem_id', null);
    $data = dbResult::getAnswer($user_id);
    $answer = array ('success' => 'true' , 'data' => $data);
    $response->getBody()->write($response->withJson($answer, 200, JSON_UNESCAPED_UNICODE));
    return $response;
});

>>>>>>> origin/master
$app->run();
?>