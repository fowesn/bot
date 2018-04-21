<?php
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

require '../vendor/autoload.php';
require '../config/config.php';

$app = new \Slim\App();

$app->get('/api/v1/problems/problem', function (Request $request, Response $response) {

    /** @var  mixed type of problem requested */
    $type = $request->getQueryParam('type', null);
    /** @var  integer id of the user who requested problem */
    $user_id = $request->getQueryParam('user_id', null);
    /** @var  string service used by the user :) (telegram, vk.com ...) */
    $service = $request->getQueryParam('service', null);

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

    //$response->getBody()->write(var_dump($data));

    $problem = $data['problem'];
    unset ($data["problem"]);
    //$response->getBody()->write(var_dump($data));
    $answer = array ('success' => 'true' , 'problem' => $problem,'data' => $data);
    $response->getBody()->write($response->withJson($answer, 200, JSON_UNESCAPED_UNICODE));
    return $response;
});


$app->get('/api/v1/solutions/solution', function (Request $request, Response $response) {

    $problem_id = $request->getQueryParam('problem_id', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);

    $user_id = dbMisc::getGlobalUserId($user_id, $service);

    $data = dbResult::getSolution($user_id, $problem_id);
    $answer = array ('success' => 'true' , 'data' => $data);
    $response->getBody()->write($response->withJson($answer, 200, JSON_UNESCAPED_UNICODE));
    return $response;
});

$app->get('/api/v1/answers/answer', function (Request $request, Response $response) {

    $problem_id = $request->getQueryParam('problem_id', null);
    //$user_id = $request->getQueryParam('user_id', null);
    //$service = $request->getQueryParam('service', null);

    $data = dbResult::getAnswer($problem_id);

    $answer = array ('success' => 'true' , 'data' => $data);
    $response->getBody()->write($response->withJson($answer, 200, JSON_UNESCAPED_UNICODE));

    return $response;
});

$app->post('/api/v1/answers/answer', function (Request $request, Response $response) {

    $problem_id = $request->getQueryParam('problem_id', null);
    $user_answer = $request->getQueryParam('user_answer', null);
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);

    $user_id = dbMisc::getGlobalUserId($user_id, $service);
    dbAssignment::assignAnswer($user_id, $problem_id, $user_answer);

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
    $user_id = $request->getQueryParam('user_id', null);
    $service = $request->getQueryParam('service', null);
    $resource_type_code = $request->getQueryParam('resource', null);

    $user_id = dbMisc::getGlobalUserId($user_id, $service);
    dbResource::setPreferredResource($user_id, $resource_type_code);
});

$app->get('/api/v1/problems/problem_types/problem_type', function (Request $request, Response $response) {

    $data = dbMisc::getProblemTypes();
    $answer = array ('success' => 'true' , 'data' => $data);
    $response->getBody()->write($response->withJson($answer, 200, JSON_UNESCAPED_UNICODE));
    return $response;

});

$app->get('/api/v1/test', function (Request $request, Response $response) {

    $problem_id = $request->getQueryParam('problem_id', null);
    $user_id = $request->getQueryParam('user_id', null);
    $answer = $request->getQueryParam('answer', null);

    dbAssignment::assignAnswer($user_id, $problem_id, $answer);

});

$app->run();
?>