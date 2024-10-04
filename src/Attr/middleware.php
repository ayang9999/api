<?php
namespace Ayang\ApiManager\Attr;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class middleware
{
    public function __construct(
        public array $middlewares = [],
        public array $exclude_middlewares = [],
    )
    {
    }
}