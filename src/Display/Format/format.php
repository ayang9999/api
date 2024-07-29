<?php
namespace Ayang\ApiManager\Display\Format;

use Ayang\ApiManager\apiEntity;

interface format {

    public function format(apiEntity $apiEntity) : string;
    public function getExtension() : string;
}