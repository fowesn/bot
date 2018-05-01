<?php
use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

require '../vendor/autoload.php';
require '../config/config.php';

$app = new \Slim\App();

// Виталик
$app->get('/api/v1/problems/problem', function (Request $request, Response $response) {

});

// Виталик
$app->get('/api/v1/solutions/solution', function (Request $request, Response $response) {

});

// Виталик
$app->get('/api/v1/answers/answer', function (Request $request, Response $response) {

});

// Андрей
$app->post('/api/v1/answers/answer', function (Request $request, Response $response) {

});

// Андрей
$app->get('/api/v1/resources/resource', function (Request $request, Response $response) {

});

// Андрей
$app->put('api/v1/resources/resource', function (Request $request, Response $response)
{

});

// Андрей
$app->get('/api/v1/problems/problem_types/problem_type', function (Request $request, Response $response) {

});

$app->run();
?>