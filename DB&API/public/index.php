<?php

use \Psr\http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App();


$app->get('/api/v1/problems/problem', function (Request $request, Response $response) {

});


$app->get('/api/v1/solutions/solution', function (Request $request, Response $response) {

});


$app->get('/api/v1/answers/answer', function (Request $request, Response $response) {

});


$app->post('/api/v1/answers/answer', function (Request $request, Response $response) {

});


$app->get('/api/v1/resources/resource', function (Request $request, Response $response) {

});


$app->put('api/v1/resources/resource', function (Request $request, Response $response)
{

});


$app->get('/api/v1/problems/problem_types/problem_type', function (Request $request, Response $response) {

});


$app->run();
?>