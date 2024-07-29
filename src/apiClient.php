<?php

namespace Ayang\ApiManager;

use Ayang\ApiManager\Attr\api;
use Ayang\ApiManager\Attr\param;
use Ayang\ApiManager\Attr\request;
use Ayang\ApiManager\Attr\response;
use Ayang\ApiManager\Attr\respField;
use Ayang\ApiManager\Attr\header;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;

class apiClient
{

    public array $successHttpLog = [];
    public array $failHttpLog = [];
    public function __construct( public \GuzzleHttp\Client $client)
    {
    }

    public function handle(apiEntity $apiEntity, $requestData = []) : bool
    {
        $hasError = false;
        foreach ($apiEntity->requestList as $request) {
            if (! $request->needRequest) {
                continue;
            }
            if ($requestData) {
                $request->request = array_merge($request->request, $requestData);
            }
            $method = $request->method ?? $apiEntity->api->method;
            $path = $request->url ?? $apiEntity->api->path;
            $options = [RequestOptions::HTTP_ERRORS => true];
            if ($method === "GET") {
                $options['query'] = $request->request;
            }else {
                if ($apiEntity->api->type == api::TYPE_JSON) {
                    $options['json'] = $request->request;
                }else {
                    $options['form_params'] = $request->request;
                }
            }
            if ($request->headers) {
                $options['headers'] = $request->headers;
            }
            try {
                $resp = $this->client->request($method, $path, $options);
            }catch (RequestException $exception){
                if ($exception->hasResponse()) {
                    $resp = $exception->getResponse();
                }else {
                    throw $exception;
                }
            }
            $content = $resp->getBody()->getContents();
            $resp->getBody()->rewind();
            $response = new response($content, $resp->getStatusCode(), $resp->getHeaders(), $request->right);
            $status = $resp->getStatusCode();
            if ($status >= 400 && $request->right) {
                $this->failHttpLog[] = [$apiEntity->api, $request, $response, 'failMsg' => "resp is {$status}"];
                $hasError = true;
            }else {
                if (isset($request->respRules) && ! $request->respRules->check($request, $response)) {
                    $this->failHttpLog[] = [$apiEntity->api, $request, $response, "failMsg" => $request->respRules->getMessage()];
                    $hasError = true;
                }else {
                    $this->successHttpLog[] = [$apiEntity->api, $request, $response];
                    $apiEntity->responseList[] = $response;
                }
            }
        }
        return $hasError;
    }

}