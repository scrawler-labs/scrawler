<?php

namespace Scrawler\Exception;

class NotFoundException extends \Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = '404 Not Found',int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}