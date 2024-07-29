<?php
namespace Ayang\ApiManager\Attr;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class api
{
    const TYPE_FORM = 'form';
    const TYPE_JSON = 'json';

    public function __construct(
        public string $name,
        public string $path,
        public string $method,
        public string $desc = "",
        public string $category = "",
        public string $type = self::TYPE_JSON
    )
    {
        $this->method = strtoupper($this->method);
    }
}