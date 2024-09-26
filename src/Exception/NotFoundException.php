<?php

namespace Scrawler\Exception;

class NotFoundException extends \Exception
{
    public function __construct($message = '404 Not Found', $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}