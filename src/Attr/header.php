<?php
namespace Ayang\ApiManager\Attr;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class header
{
    public function __construct(
        public string $name,
        public string $desc,
    )
    {
    }
}