<?php
namespace Ayang\ApiManager\Attr;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class respField
{
    public function __construct(
        public string $name,
        public string $type,
        public string $desc,
    )
    {
    }
}