<?php

namespace Ayang\ApiManager;
use Ayang\ApiManager\Attr\api;
use Ayang\ApiManager\Attr\param;
use Ayang\ApiManager\Attr\request;
use Ayang\ApiManager\Attr\response;
use Ayang\ApiManager\Attr\respField;
use Ayang\ApiManager\Attr\header;

/**
 * a api info
 */
class apiEntity
{
    /**
     * @param api $api
     * @param array<param> $paramList
     * @param array<respField> $respFieldList
     * @param array<request> $requestList
     * @param array<response> $responseList
     * @param array<header> $headerList
     */
    public function __construct(
        public api $api,
        public array $paramList,
        public array $respFieldList,
        public array $requestList,
        public array $responseList,
        public array $headerList,
    )
    {
    }

    /**
     * @param string $className
     * @return array<self>
     * @throws \ReflectionException
     */
    static public function getByClass(string $className) : array
    {
        $ref = new \ReflectionClass($className);
        $list = [];
        foreach ($ref->getMethods() as $method) {
            if ($self = self::getByMethod($className, $method->getName())) {
                $list[] = $self;
            }
        }
        return $list;
    }

    static public function getByMethod(string $className, string $method) : self|null
    {
        $ref = new \ReflectionClass($className);
        $refFun = $ref->getMethod($method);
        if (! $api = $refFun->getAttributes(api::class)) {
            return null;
        }
        $api = $api[0]->newInstance();
        $mapNew = function (array $list) {
            return array_map(function (\Reflector $reflector) {
                return $reflector->newInstance();
            }, $list);
        };
        $paramList = $mapNew($refFun->getAttributes(param::class));
        $respFieldList = $mapNew($refFun->getAttributes(respField::class));
        $requestList = $mapNew($refFun->getAttributes(request::class));
        $responseList = $mapNew($refFun->getAttributes(response::class));
        $headerList = $mapNew($refFun->getAttributes(header::class));
        return new self($api, $paramList, $respFieldList, $requestList, $responseList, $headerList);
    }

}