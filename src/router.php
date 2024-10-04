<?php


namespace Ayang\ApiManager;


use Ayang\ApiManager\Attr\middleware;

class router
{
    public string $url;
    public string $method;
    public ?middleware $middleware;
}