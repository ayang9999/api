<?php
namespace Ayang\ApiManager\Attr;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class fullUrl
{
    public function __construct(
        public string $url,
    )
    {
    }
}