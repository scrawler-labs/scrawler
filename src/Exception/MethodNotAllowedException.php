<?php
/*
 * This file is part of the Scrawler package.
 *
 * (c) Pranjal Pandey <its.pranjalpandey@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scrawler\Exception;

class MethodNotAllowedException extends \Exception
{
    public function __construct(string $message = '405 Method Not Allowed', int $code = 405, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
