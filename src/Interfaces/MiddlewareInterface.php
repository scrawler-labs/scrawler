<?php
/*
 * This file is part of the Scrawler package.
 *
 * (c) Pranjal Pandey <its.pranjalpandey@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scrawler\Interfaces;

interface MiddlewareInterface
{
    public function run(\Scrawler\Http\Request $request, \Closure $next): \Scrawler\Http\Response;
}
