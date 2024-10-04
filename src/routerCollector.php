<?php

namespace Ayang\ApiManager;

use Ayang\ApiManager\Attr\api;
use Ayang\ApiManager\Attr\apiPrefix;
use Ayang\ApiManager\Attr\fullUrl;
use Ayang\ApiManager\Attr\middleware;

class routerCollector
{

    protected array $middlewareMap = [];
    protected array $classList = [];
    protected string $prefix = '/';

    /**
     * @var array<router> $routerList
     */
    protected array $routerList = [];

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    protected static function getClassesFromDirectory($directory): array
    {
        $classes = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // 获取文件名
                $namespace = self::getNamespaceFromFile($file->getPathname());
                $className = basename($file->getFilename(), '.php');
                if (class_exists($namespace . '\\'. $className, true)) {
                    $classes[] = $namespace . '\\'. $className;
                }
                // 载入文件
            }
        }
        return $classes;
    }

    protected static function getNamespaceFromFile($filePath): ?string
    {
        // 检查文件是否存在
        if (!file_exists($filePath)) {
            return null;
        }
        // 读取文件内容
        $fileContent = file_get_contents($filePath);
        // 使用正则表达式匹配命名空间
        if (preg_match('/^\s*namespace\s+([^;]+);/m', $fileContent, $matches)) {
            return trim($matches[1]); // 返回匹配到的命名空间
        }
        return null; // 如果没有找到命名空间
    }

    public function setDirectory(string $directory): self
    {
        $this->classList = self::getClassesFromDirectory($directory);
        return $this;
    }

    public function setMiddleware(string $prefix, array|string $middlewares) : self
    {
        $this->middlewareMap[$prefix] = is_array($middlewares) ? $middlewares : [$middlewares];
        return $this;
    }

    public function setMiddlewareMap(array $middlewareMap) : self
    {
        $this->middlewareMap = array_merge($this->middlewareMap, $middlewareMap);
        return $this;
    }

    /**
     * @param string $className
     * @return array<self>
     * @throws \ReflectionException
     */
    public function getByClass(string $className) : array
    {
        $ref = new \ReflectionClass($className);
        $list = [];

        foreach ($ref->getMethods() as $method) {
            if ($router = self::getByMethod($ref, $method->getName())) {
                $list[] = $router;
            }
        }
        return $list;
    }

    public function getByMethod(\ReflectionClass $classRef, string $method) : ?router
    {
        $ref = $classRef;
        if ($apiPrefix = $ref->getAttributes(apiPrefix::class)) {
            /** @var apiPrefix $apiPrefix */
            $apiPrefix = $apiPrefix[0]->newInstance();
        }
        $refFun = $ref->getMethod($method);
        /** @var api $api */
        if (! $api = $refFun->getAttributes(api::class)) {
            return null;
        }
        $api = $api[0]->newInstance();

        /** @var fullUrl $url */
        if ( $url = $refFun->getAttributes(fullUrl::class)) {
            $url = $url[0]->newInstance();
        }else {
            if ($apiPrefix) {
                $api->path = $apiPrefix->prefix . "/" .$api->path;
            }
            $api->path = $this->prefix . "/"  . $api->path;
        }
        /** @var middleware $middleware */
        if ($middleware = $refFun->getAttributes(middleware::class)) {
            $middleware = $middleware[0]->newInstance();
        }



        $router = new router();
        $router->method = $api->method;
        $router->url = $url ? $url->url : $api->path;

        $router->url = preg_replace('/\/+/', '/', $router->url);

        $router->middleware = is_object($middleware) ? $middleware : new middleware();

        foreach ($this->middlewareMap as $prefix => $middlewares) {
            if (str_starts_with($router->url, $prefix)) {
                $router->middleware->middlewares = array_merge($middlewares, $router->middleware->middlewares);
            }
        }

        $router->middleware->middlewares = array_diff($router->middleware->middlewares, $router->middleware->exclude_middlewares);

        return $router;
    }

    /**
     * @return router[]|routerCollector[]
     */
    public function load(): array
    {
        krsort($this->middlewareMap);
        foreach ($this->classList as $class) {
            $this->routerList = array_merge($this->routerList, $this->getByClass($class));
        }
        return $this->routerList;
    }
}