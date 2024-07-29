<?php
namespace Ayang\ApiManager\Attr;
use attr\rules\respRules;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class request
{
    public function __construct(public array $request = [],
                                public array $headers = [],
                                public ?string $method = null,
                                public bool $needRequest = true,
                                public ?string $url = null,
                                public $right = true,
                                public ?respRules $respRules = null
    )
    {
        if ($this->method) {
            $this->method = strtoupper($this->method);
        }
    }

}