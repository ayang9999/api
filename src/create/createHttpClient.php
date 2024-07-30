<?php
namespace Ayang\ApiManager\create;

use Ayang\ApiManager\apiEntity;
use Ayang\ApiManager\Attr\request;
use GuzzleHttp\Client;

class createHttpClient
{

    public Client $client;


    public string $namespace = "";
    public string $returnType = "\Psr\Http\Message\ResponseInterface";


    /**
     * @param array<apiEntity> $apiEntityList
     * @return string
     */
    public function formatClass(array $apiEntityList): string
    {

        $functions = "";
        foreach ($apiEntityList as $apiEntity) {
            $functions .= ("\n" . $this->formatFunction($apiEntity));
        }

        $tpl = <<<TPL
<?php

namespace {$this->namespace};
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

trait apiClient
{
    
    public Client \$client;
    
    public function handel(string \$m, string \$url, array \$p) : {$this->returnType}
    {
        return \$this->client->request(\$m, \$url, \$p);
    }
    
    {$functions}
}    
TPL;
        return $tpl;

    }


    public function formatFunction(apiEntity $apiEntity) : string
    {
        $funName = $this->getFuncName($apiEntity->api->path, $apiEntity->api->method);
        $argv = $this->getArgvs($apiEntity);
        if ($apiEntity->api->method == 'GET') {
            $p = "['json' => func_get_args()]";
        }else {
            $p = "['query' => func_get_args()]";
        }
        $note = $this->getNote($apiEntity);
        $tmp = <<<FUNC
    {$note}
    public function {$funName}({$argv})
    {
        \$url = '{$apiEntity->api->path}';
        return \$this->handel('{$apiEntity->api->method}', '{$apiEntity->api->path}', {$p});
    }
FUNC;
        return $tmp;
    }

    private function getNote(apiEntity $apiEntity) : string
    {
        $list = ["\n     /**"];
        foreach ($apiEntity->paramList as $param) {
            $list[] = "     * @param {$param->type} \${$param->name} - \${$param->name} {$param->desc}";
        }
        $list[] = "     */";
        return implode("\n", $list);
    }


    private function getFuncName(string $url, string $m) : string
    {
        return strtolower($m) . "_" . str_replace("/", "_", $url);
    }

    private function getArgvs(apiEntity $apiEntity) : string
    {
        $argvs = [];
        foreach ($apiEntity->paramList as $param) {
            [$type, $value] = match ($param->type) {
                'bool' => ['bool', boolval($param->value)],
                'int' => ['int', intval($param->value)],
                'float', 'double' => ['float', floatval($param->value)],
                'array', 'list', 'object' => ['array', $param->value],
                default => ['string', strval($param->value)],
            };
            $argv = $type . ' $' . $param->name;
            if (!is_null($param->value)) {
                $def = var_export($value, true);
                $argv .= " = {$def}";
            }elseif (!$param->must) {
                $argv .= " = null";
            }
            $argvs[] = $argv;
        }
        return implode(", ", $argvs);
    }

}