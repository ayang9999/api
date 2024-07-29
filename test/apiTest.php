<?php

use Ayang\ApiManager\apiEntity;
use GuzzleHttp\Promise\FulfilledPromise;

$dir = "/tmp/aYangDocTest";

if (is_dir($dir)) {
    echo ("rm $dir");
    exit();
}

it("文档列表", function () use ($dir) {


    $cateFile = "/tmp/aYangDocTest/cate.md";

    $apiList = apiEntity::getByClass(\Ayang\ApiManager\Test\example\userController::class);
    expect($apiList)->toBeArray()->toHaveCount(4);
    $maker = new \Ayang\ApiManager\Doc\documentMaker($apiList, new \Ayang\ApiManager\Display\Format\markdownFormat(), $dir, $cateFile);
    $display = new \Ayang\ApiManager\display($maker);

    $maker->showChange();

    expect($maker->runtimeLog['api']['new'])->toBeArray()->toHaveCount(4);

    $maker->makeAll();

    $maker = new \Ayang\ApiManager\Doc\documentMaker($apiList, new \Ayang\ApiManager\Display\Format\markdownFormat(), $dir, $cateFile);

    $maker->showChange();

    expect($maker->runtimeLog['api']['new'])->toBeArray()->toHaveCount(0)
        ->and($maker->runtimeLog['api']['change'])->toBeArray()->toHaveCount(0)
        ->and($maker->runtimeLog['api']['del'])->toBeArray()->toHaveCount(0);

    $maker->makeCategoryFile();

    $apiList = apiEntity::getByClass(\Ayang\ApiManager\Test\example\userControllerMod::class);

    $maker = new \Ayang\ApiManager\Doc\documentMaker($apiList, new \Ayang\ApiManager\Display\Format\markdownFormat(), $dir, $cateFile);


    $display = new \Ayang\ApiManager\display($maker);

    $maker->showChange();

    expect($maker->runtimeLog['api']['new'])->toBeArray()->toHaveCount(2)
        ->and($maker->runtimeLog['api']['change'])->toBeArray()->toHaveCount(1)
        ->and($maker->runtimeLog['api']['del'])->toBeArray()->toHaveCount(2);

//    $display->printLog();

});


it("api请求", function () use ($dir) {


    $cateFile = "/tmp/aYangDocTest/cate.md";

    /** @var array<apiEntity> $apiList */
    $apiList = apiEntity::getByClass(\Ayang\ApiManager\Test\example\userController::class);
    $maker = new \Ayang\ApiManager\Doc\documentMaker($apiList, new \Ayang\ApiManager\Display\Format\markdownFormat(), $dir, $cateFile);
    $resp = new GuzzleHttp\Psr7\Response(200,[],'{"code":0}');
    $guzzleClient = Mockery::mock(GuzzleHttp\Client::class);
    $guzzleClient->allows([
        "request" => $resp//new GuzzleHttp\Psr7\Response(200,[],'{"code":0}')
    ]);
    $apiClient = new \Ayang\ApiManager\apiClient($guzzleClient);
    $maker->setApiClient($apiClient);
    $maker->makeAll();

    expect($apiClient->successHttpLog)->toBeArray()->toHaveCount(3);

    $maker = new \Ayang\ApiManager\Doc\documentMaker($apiList, new \Ayang\ApiManager\Display\Format\markdownFormat(), $dir, $cateFile);
    $guzzleClient = Mockery::mock(GuzzleHttp\Client::class);
    $guzzleClient->allows([
        "request" => new GuzzleHttp\Psr7\Response(400,[],'{"code":0}')
    ]);
    $apiClient = new \Ayang\ApiManager\apiClient($guzzleClient);
    $maker->setApiClient($apiClient);
    $maker->makeAll();

    expect($apiClient->successHttpLog)->toBeArray()->toHaveCount(1)
        ->and($apiClient->failHttpLog)->toBeArray()->toHaveCount(2);


    $apiList = apiEntity::getByClass(\Ayang\ApiManager\Test\example\userControllerMod::class);
    $maker = new \Ayang\ApiManager\Doc\documentMaker($apiList, new \Ayang\ApiManager\Display\Format\markdownFormat(), $dir, $cateFile);

    $maker->showChange();

    expect($maker->runtimeLog['api']['new'])->toBeArray()->toHaveCount(2)
        ->and($maker->runtimeLog['api']['change'])->toBeArray()->toHaveCount(1)
        ->and($maker->runtimeLog['api']['del'])->toBeArray()->toHaveCount(2);

    $maker->makeCategoryFile();
});

