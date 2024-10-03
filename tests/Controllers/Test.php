<?php

namespace Tests\Controllers;

class Test
{
    public function getIndex()
    {
        return 'Hello World';
    }

    public function getTest()
    {
        return 'Hello World';
    }

    public function getException()
    {
        throw new \Exception('Exception');
    }
}