<?php
namespace Ayang\ApiManager\Attr;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class apiPrefix
{
    public function __construct(
        public string $prefix,
    )
    {
    }
}