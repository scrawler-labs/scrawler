<?php

namespace Scrawler\Exception;

class MethodNotAllowedException extends \Exception
{
    /**
     * 
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = '405 Method Not Allowed', int $code = 405, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}