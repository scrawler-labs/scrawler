<?php

namespace Tests\Controllers;

class Main
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