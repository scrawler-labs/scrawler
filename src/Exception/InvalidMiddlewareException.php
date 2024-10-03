<?php

namespace Scrawler\Exception;

class InvalidMiddlewareException extends \Exception
{
    /**
     * 
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'Invalid Middleware', int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}