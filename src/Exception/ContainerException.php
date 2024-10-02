<?php

namespace Scrawler\Exception;

class ContainerException extends \Exception
{
    /**
     * ContainerException constructor.
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message = 'Container Exception', int $code = 500, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}