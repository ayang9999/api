<?php
namespace Ayang\ApiManager\Attr\Rules;
use attr\request;
use attr\response;

interface respRules
{
    public function check(request $request, response $response) : bool;
    public function getMessage() : string;
}