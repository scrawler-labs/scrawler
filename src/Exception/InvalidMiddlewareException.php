<?php

namespace Scrawler\Exception;

class InvalidMiddlewareException extends \Exception
{
    public function __construct(string $message = 'Invalid Middleware', int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
