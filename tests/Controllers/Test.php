<?php

namespace Tests\Controllers;

class Test
{
    public function getIndex(): string
    {
        return 'Hello World';
    }

    public function getTest(): string
    {
        return 'Hello World';
    }

    public function getException(): never
    {
        throw new \Exception('Exception');
    }
}
