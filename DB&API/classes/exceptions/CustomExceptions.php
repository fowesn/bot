<?php
/**
 * Created by PhpStorm.
 * User: Anton
 * Date: 01.05.2018
 * Time: 20:35
 */

class UserException extends Exception
{
    // статус JSON-ответа
    private $jsonStatus;

    function __construct($jsonStatus, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->jsonStatus = $jsonStatus;
    }

    public function __get($name)
    {
        switch ($name)
        {
            case 'jsonStatus':
                return $this->jsonStatus;
                break;
        }
    }

    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'jsonStatus':
                $this->jsonStatus = $value;
                break;
        }
    }
}

class RequestException extends Exception
{
    // статус JSON-ответа
    private $jsonStatus = -1;

    public function __construct($jsonStatus, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->jsonStatus = $jsonStatus;
    }

    public function __get($name)
    {
        switch ($name)
        {
            case 'jsonStatus':
                return $this->jsonStatus;
                break;
        }
    }

    public function __set($name, $value)
    {
        switch ($name)
        {
            case 'jsonStatus':
                $this->jsonStatus = $value;
                break;
        }
    }
}
