<?php
namespace Ayang\ApiManager\Display\Format;

use Ayang\ApiManager\apiEntity;
use Ayang\ApiManager\Attr\api;
use Ayang\ApiManager\Attr\param;
use Ayang\ApiManager\Attr\request;
use Ayang\ApiManager\Attr\response;
use Ayang\ApiManager\Attr\respField;
use Ayang\ApiManager\Attr\header;
class markdownFormat implements format {

    public function format(apiEntity $apiEntity) : string
    {
        return $this->getMd($apiEntity);
    }

    public function getExtension() : string
    {
        return 'md';
    }


    protected function getPath(apiEntity $apiEntity) : string
    {
        return $apiEntity->api->path . " {$apiEntity->api->method}";
    }

    protected function getParams(apiEntity $apiEntity) : string
    {
        return implode("\n", array_map(function (param $param) {
            $must = $param->must ? "是" : "否";
            return "|{$param->name}|{$param->type}|{$must}|{$param->desc}|";
        }, $apiEntity->paramList));
    }

    protected function getRespFields(apiEntity $apiEntity) : string
    {
        return implode("\n", array_map(function (respField $param) {
            return "|{$param->name}|{$param->type}|{$param->desc}|";
        }, $apiEntity->respFieldList));
    }

    protected function getRequests(apiEntity $apiEntity) : string
    {
        return implode("\n", array_filter(array_map(function (request $request) use ($apiEntity) {

            if (! $request->request && !$request->url) {
                return "";
            }

            if ($apiEntity->api->method === "GET") {
                $url = trim($request->url ?: $apiEntity->api->path . "?" . http_build_query($request->request), '?');
                return "```http request
{$url}
```";
            }

            if ($apiEntity->api->type === api::TYPE_FORM && $request->request) {
                $body = http_build_query($request->request);
                return "{$request->url}```http request
{$body}
```";
            }

            $reqStr = json_encode($request->request, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);

            return "```json
{$reqStr}
```";
        }, $apiEntity->requestList)));
    }

    protected function getResponses(apiEntity $apiEntity) : string
    {
        return implode("\n", array_map(function (response $response) {
            json_decode($response->body) && $response->body = json_encode(json_decode($response->body), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
            return "```json
{$response->body}
```";
        }, $apiEntity->responseList));
    }

    protected function getMd(apiEntity $apiEntity) : string
    {
        $url = $this->getPath($apiEntity);
        $params = $this->getParams($apiEntity);
        $result = $this->getRespFields($apiEntity);
        $requests = $this->getRequests($apiEntity);
        $responses = $this->getResponses($apiEntity);

        $tmp = <<<TMP
### {$apiEntity->api->name}
    {$apiEntity->api->desc}
#### 路由 
    {$url}
#### 请求参数说明
| 字段   | 类型  | 是否必填 | 说明 |
|:-----|:----|:-----|----|
{$params}
#### 返回参数说明 
| 字段   | 类型  | 说明 |
|:-----|:----|----|
{$result}

#### 请求实例
{$requests}

#### 响应实例
{$responses}
TMP;
        return $tmp;
    }
}