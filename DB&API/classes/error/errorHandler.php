<?php
/**
 * Created by PhpStorm.
 * User: Anthony
 * Date: 11.03.2019
 * Time: 9:52
 */

/*
 * Здесь живут обработчики НЕпользовательских ошибок:
 * неверные параметры, неверный запрос, ошибка сервера и т.д.
 */


// 400 - обязательный параметр имеет неверный тип
class invalidParameterHandler
{
    public function __invoke($response, $parameterName, $parameterType, $expectedType)
    {
        return $response->withJson(array(
                'status' => ERROR,
                'data' => [
                    'message'=> 'Parameter <' . $parameterName . '> is of type ' . $parameterType . ', type ' . $expectedType . ' expected!'
                ]), 400, JSON_UNESCAPED_UNICODE);
    }
}

// 400 - отсутствует обязательный параметр
class missingParameterHandler
{
    public function __invoke($response, $parameterName)
    {
        return $response->withJson(array(
            'status' => ERROR,
            'data' => [
                'message'=> 'Missing required parameter <' . $parameterName . '>!'
            ]), 400, JSON_UNESCAPED_UNICODE);
    }
}

// 405 - данный ресурс не поддерживает указанное действие
class notAllowedHandler
{
    public function __invoke($request, $response, $methods)
    {
        return $response->withJson(array(
            'status' => ERROR,
            'data' => [
                'message'=> 'Method must be one of: ' . implode(', ', $methods) . '!'
            ]), 405, JSON_UNESCAPED_UNICODE);
    }
}

// 422 - данные семантически неверны
// пока что этот обработчик реагирует только на лишние параметры в запросе
class unprocessableEntityHandler
{
    public function __invoke($response, $parameterName)
    {
        return $response->withJson(array(
            'status' => ERROR,
            'data' => [
                'message'=> 'Parameter <' . $parameterName . '> is unprocessable!'
            ]), 422, JSON_UNESCAPED_UNICODE);
    }
}

// 500 - внутренняя ошибка сервера
class serverErrorHandler
{
    public function __invoke($response)
    {
        return $response->withJson(array(
            'status' => ERROR,
            'data' => [
                'message' => 'Server error occurred, please try again later!'
            ]), 500, JSON_UNESCAPED_UNICODE);
    }
}