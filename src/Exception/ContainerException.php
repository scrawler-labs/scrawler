<?php

namespace Scrawler\Exception;

class ContainerException extends \Exception
{
    public function __construct($message = 'Container Exception', $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}