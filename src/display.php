<?php

namespace Ayang\ApiManager;
use Ayang\ApiManager\Doc\documentMaker;
use Ayang\ApiManager\Attr\api;
use Ayang\ApiManager\Attr\param;
use Ayang\ApiManager\Attr\request;
use Ayang\ApiManager\Attr\response;
use Ayang\ApiManager\Attr\respField;
use Ayang\ApiManager\Attr\header;
use Inhere\Console\Util\Show;

class display
{

    public function __construct(
        protected ?documentMaker $documentMaker = null,
        protected ?apiClient $apiClient = null)
    {
    }

    public function printLog() : void
    {
        $show = [];
        foreach ($this->documentMaker->runtimeLog['api'] as $type => $value) {
            if (!$value) {
                continue;
            }
            foreach ($value as $k => $v) {
                if ($type == "del") {
                    $show['del api'][] = $v;
                    continue;
                }
                if ($type == "ignoreDiff") {
                    $show['ignore change api'][] = $v;
                    continue;
                }
                $info = $v['method'] ." ". $v['path'] . " " . $v['name'];
                if ($type == "none") {
                    $show['no change api'][] = $info;
                }
                if ($type == "new") {
                    $show['new api'][] = $info;
                    $show['new file'][] = $v['file'];
                }
                if ($type == "change") {
                    $show['change api'][] = $info;
                    $show['change file'][] = $v['file'];
                }
            }
        }
        $show['error'] = $this->documentMaker->runtimeLog['error'];
        if ($this->documentMaker->runtimeLog['category_diff']) {
            Show::info(" category_diff\n" . $this->documentMaker->runtimeLog['category_diff']);
        }
        Show::mList($show);
    }

    public function listApi() : void
    {
        $list = $this->documentMaker->getApiEntityList();
        $list = array_values($list);
        $showList = [];
        foreach ($list as $seq => $apiEntity) {
            $api = $apiEntity->api;
            $showList[ substr($seq . "   ", 0, 4) . substr($api->method . "     ", 0, 7) . $api->path] = $api->name;
        }
        Show::aList($showList);
    }
}