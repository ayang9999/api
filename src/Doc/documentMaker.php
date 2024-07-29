<?php
namespace Ayang\ApiManager\Doc;

use Ayang\ApiManager\apiClient;
use Ayang\ApiManager\apiEntity;
use Ayang\ApiManager\Attr\api;
use Ayang\ApiManager\Attr\param;
use Ayang\ApiManager\Attr\request;
use Ayang\ApiManager\Attr\response;
use Ayang\ApiManager\Attr\respField;
use Ayang\ApiManager\Attr\header;
use Ayang\ApiManager\Display\Format\format;

use SebastianBergmann\Diff\Differ;

class documentMaker
{
    /**
     * @var array<apiEntity> $apiEntityList
     */
    protected array $apiEntityListMap;
    protected string $apiDigestFile;
    protected array $digestData = [];
    protected ?apiClient $apiClient = null;

    protected Differ $differ;

    protected string $fileExt;

    public array $runtimeLog = [
        "error" => [],
        "category_diff" => "",
        "api" => [
            'change' => [],
            'none' => [],
            'new' => [],
            'ignoreDiff' => [],
            'del' => []
        ],
    ];

    public function __construct(
                                array $apiEntityList,
                                protected format $format,
                                protected string $outputDir,
                                protected ?string $categoryFilePath = null)
    {
        $this->outputDir = rtrim($this->outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->apiDigestFile = $this->outputDir . "apiDigest.json";
        if (is_file($this->apiDigestFile)) {
            $data = file_get_contents($this->apiDigestFile);
            $this->digestData = json_decode($data, true) ?: [];
        }else {
            $this->digestData = [];
        }
        $builder = new \SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder("");
        $this->differ = new Differ($builder);
        $this->fileExt = $this->format->getExtension();
        $this->setApiEntityList($apiEntityList);
    }


    /**
     * @param apiClient $apiClient
     * @return $this
     */
    public function setApiClient(apiClient $apiClient): self
    {
        $this->apiClient = $apiClient;
        return $this;
    }

    /**
     * @param array<apiEntity> $apiEntityList
     * @return $this
     */
    protected function setApiEntityList(array $apiEntityList) : self
    {
        $this->apiEntityListMap = [];
        foreach ($apiEntityList as $apiEntity) {
            $this->apiEntityListMap[$this->getDigestDataKey($apiEntity->api->method, $apiEntity->api->path)] = $apiEntity;
        }
        return $this;
    }
    
    public function getApiEntityList() : array
    {
        return $this->apiEntityListMap;
    }

    protected function formatCategory() : array
    {
        $apis = array_column($this->apiEntityListMap, "api");
        $categoryList = [];
        /**
         * @var int $key
         * @var api $api
         */
        foreach ($apis as $key => $api) {
            $categoryList[] = trim($api->category, '/') . '/' . $api->name;
        }
        $newCate = [];
        $fun = function (array $titles, $newCate, $cate) use (&$fun) {
            if ($title = array_shift($titles)) {
                $newCate[$title] = $fun($titles, $newCate[$title] ?? [], $cate);
            }else {
                return $cate . ".{$this->fileExt}";
            }
            return $newCate;
        };
        foreach ($categoryList as $cate) {
            $cate = trim($cate, "/");
            $arr = explode("/", $cate);
            $newCate = $fun($arr, $newCate, $cate);
        }
        return $newCate;
    }

    protected function getCategoryContent() : string
    {
        $paths = $this->formatCategory();

        $map = function ($paths, $dp) use (&$map) {
            $sidebar = "";
            $kg = "   ";
            foreach ($paths as $path => $sonPaths) {
                $sidebar .= str_repeat($kg, $dp) . "- [{$path}]";
                if (is_string($sonPaths)) {
                    $sidebar .= "(api/{$sonPaths})\n";
                }else{
                    $sidebar .=  "()" . (str_repeat($kg, $dp). "\n");
                    $sidebar .= $map($sonPaths, $dp + 1);
                }
            }
            return $sidebar;
        };
        return "* [Home](README.md)\n" . $map($paths, 1);
    }

    protected function getDigestDataKey($method, $path) : string
    {
        return$key = "{$method} {$path}";
    }

    public function ignoreBaseChange() : array
    {
        $diff = [];
        /** @var apiEntity $apiInfo */
        foreach ($this->apiEntityListMap as $apiEntity) {
            $baseContent = $this->format->format($apiEntity);
            $key = $this->getDigestDataKey($apiEntity->api->method, $apiEntity->api->path);
            if ($thisData = $this->digestData[$key] ?? []) {
                $oldContent = $thisData['base_content'] ?? "";
                if ($oldContent != $baseContent) {
                    $this->digestData[$key]['base_content'] = $baseContent;
                    $diff[$thisData['name']] = ['api' => $key, 'file' => $thisData['file'], 'diff' => $this->differ->diff($oldContent, $baseContent)];
                    $this->runtimeLog['api']['ignoreDiff'][] = $key;
                }
            }
        }
        $this->putDigestData();
        return $diff;
    }

    public function delApi() : array
    {
        $delApis = ($this->digestData);
        foreach ($this->apiEntityListMap as $apiEntity) {
            $key = $this->getDigestDataKey($apiEntity->api->method, $apiEntity->api->path);
            if (isset($this->apiEntityListMap[$key])) {
                unset($delApis[$key]);
            }
        }
        $ret = [];
        foreach ($delApis as $key => $api) {
            unset($this->digestData[$key]);
            $this->runtimeLog['api']['del'][] = $ret[] = $key;
        }
        $this->putDigestData();
        return $ret;
    }


    public function showChange() : array
    {
        $delApis = array_keys($this->digestData);
        foreach ($this->apiEntityListMap as $apiEntity) {
            $content = $this->format->format($apiEntity);
            $file = $this->getFilePath($apiEntity);
            $key = $this->getDigestDataKey($apiEntity->api->method, $apiEntity->api->path);
            if (isset($this->digestData[$key])) {
                $diff = $this->differ->diff($content, $this->digestData[$key]['base_content']);
                unset($delApis[array_search($key, $delApis)]);
                if ( ($content) == $this->digestData[$key]['base_content']) {
                    $this->setApiLog("none", $apiEntity, $file, $diff);
                }else {
                    $this->setApiLog("change", $apiEntity, $file, $diff);
                }
            }else {
                $this->setApiLog("new", $apiEntity, $file, "");
            }
        }
        $this->runtimeLog['api']['del'] = $delApis;
        return $this->runtimeLog['api'];
    }

    protected function setApiLog($type, apiEntity $apiEntity, string $file = "", string $diff = "") : void
    {
        $map = [
            "none" => "The document content remains unchanged",
            "change" => "Changes in document content",
            "new" => "New document",
        ];
        $apiInfo = ["path" => $apiEntity->api->path, "name" => $apiEntity->api->name, "method" => $apiEntity->api->method, "file" => $file, "info" => $map[$type], 'diff' => $diff];
        $this->runtimeLog['api'][$type][] = $apiInfo;
    }

    public function makeCategoryFile() : string
    {
        $categoryContent = ($this->getCategoryContent());
        $categoryFile = $this->categoryFilePath;
        $oldContent = is_file($categoryFile) ? file_get_contents($categoryFile) : "";
        $diff = $this->differ->diff($oldContent, $categoryContent) ?: "";
        $this->runtimeLog['category_diff'] = $diff;
        file_put_contents($this->categoryFilePath, $categoryContent);
        return $diff;
    }

    public function makeOne($method, $url) : bool
    {
        $ret = $this->makeItem($method, $url);
        $this->putDigestData();
        return $ret;
    }


    protected function makeItem($method, $url) : bool
    {
        $method = strtoupper($method);
        if (! isset($this->apiEntityListMap[$key = $this->getDigestDataKey($method, $url)])) {
            $this->runtimeLog['error'][] = "No api exists {$method} {$url} !";
            return false;
        }
        $apiEntity = $this->apiEntityListMap[$key];
        $baseContent = $this->format->format($apiEntity);
        $hasError = $this->apiClient?->handle($apiEntity);

        if ($hasError === true) {
            $this->setApiLog("none", $apiEntity, "");
            $this->runtimeLog['error'][] = "{$key} : http request error!";
            return false;
        }

        $content = $this->format->format($apiEntity);
        $file = $this->getFilePath($apiEntity);

        if (is_file($file)) {
            if ( file_get_contents($file) == $content) {
                $this->setApiLog("none", $apiEntity, $file);
            }else {
                $this->setApiLog("change", $apiEntity, $file);
            }
        }else {
            $this->setApiLog("new", $apiEntity, $file);
        }
        if (! file_put_contents($file, $content)) {
            $this->runtimeLog['error'][] = "{$file} Write failed!";
            return false;
        }
        $this->setDigestData($key, $baseContent, $file, $apiEntity->api->name, $apiEntity);
        return true;
    }

    protected function setDigestData(string $key, string $baseContent = null, string $file = null, string $name = null, apiEntity $apiEntity = null) : self
    {
        $this->digestData[$key] = [
            'base_content' => ($baseContent) ?? $this->digestData[$key]['base_content'],
            'file' => $file ?? $this->digestData[$key]['file'],
            'name' => $name ?? $this->digestData[$key]['name'],
            'requestList' => $apiEntity?->requestList,
            'responseList' => $apiEntity?->responseList,
        ];
        return $this;
    }


    /**
     * @param array<array> $routerList
     * @return void
     */
    public function make(array $routerList) : void
    {
        foreach ($routerList as $router) {
            [$method , $url] = $router;
            $this->makeItem($method, $url);
        }
        $this->putDigestData();
    }

    /**
     * @param array<array> $routerList
     * @return void
     */
    public function makeAll() : void
    {
        foreach ($this->apiEntityListMap as $apiEntity) {
            [$method , $url] = [$apiEntity->api->method, $apiEntity->api->path];
            $this->makeItem($method, $url);
        }
        $this->putDigestData();
    }

    protected function getFilePath(apiEntity $apiEntity) : string
    {
        $outputDir = $this->getCategoryDir($apiEntity->api->category);
        return $outputDir . $apiEntity->api->name . "." . $this->fileExt;
    }

    protected function getCategoryDir(string $category = "") : string
    {
        $path = $category ? ($this->outputDir . trim($category, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) : $this->outputDir;
        return $this->mkdir($path);
    }

    /**
     * @param $dir
     * @return string
     * @throws \Exception
     */
    protected function mkdir($dir) : string
    {
        if (! is_dir($dir)) {
            if (! mkdir($dir, 0777, true)) {
                throw new \Exception("Directory creation failed");
            }
            chmod($dir, 0777);
        }
        return $dir;
    }

    protected function putDigestData() : void
    {
        file_put_contents($this->apiDigestFile, json_encode($this->digestData, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }

}