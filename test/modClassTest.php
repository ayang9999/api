<?php

use Ayang\ApiManager\apiEntity;
use GuzzleHttp\Promise\FulfilledPromise;




it("创建http请求", function () {

//    $apiList = apiEntity::getByClass(\Ayang\ApiManager\Test\example\userController::class);
//
//    $tmp = new \Ayang\ApiManager\create\createHttpClient();
//    $tmp->namespace = 'Ayang\ApiManager\Test\tmp';
//    $str = $tmp->formatClass($apiList);
//


})->skip("暂时跳过");

test('测试', function () {

// 使用示例
    $directory = __DIR__; // 更改为你的目录
    $routerList = (new \Ayang\ApiManager\routerCollector())->setDirectory($directory)->load();

    expect($routerList)->toBeArray()
        ->and($routerList[0]->url)->toBe('/user/get')
    ->and($routerList[1]->middleware->middlewares)->toBe(['check_login']);

    $routerList = (new \Ayang\ApiManager\routerCollector())->setDirectory($directory)
        ->setMiddleware("/api/user", [
        'check_login2'
    ])->setMiddleware("/", "api")->setPrefix("/api")->load();

    expect($routerList)->toBeArray()
        ->and($routerList[0]->url)->toBe('/api/user/get')->and($routerList[0]->middleware->middlewares)->toBe(['api', 'check_login2', 'check_login'])
        ->and($routerList[1]->middleware->middlewares)->toBe(['api', 'check_login'])
        ->and($routerList[1]->url)->toBe('/save')->and($routerList[1]->method)->toBe('POST')
        ->and($routerList[2]->middleware->middlewares)->toBe(['api']);

});