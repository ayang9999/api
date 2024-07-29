<?php
namespace Ayang\ApiManager\Attr;
use Attribute;


#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class response
{
    public function __construct(public string $body, public int $status = 200, public array $headers = [], bool $right = true)
    {
    }
}