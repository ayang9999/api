<?php
namespace Ayang\ApiManager\Attr\Rules;
use attr\request;
use attr\response;
use Attribute;
use lib\units\funs;

#[Attribute(Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
class simpleRules implements respRules
{

    public array|\stdClass $respData = [];
    public function __construct(public array $rules, public string $message = "")
    {
    }

    public function check(request $request, response $response) : bool
    {
        $this->respData = json_decode($response->body) ?: [];
        foreach ($this->rules as $rule) {
            list($fields, $rules) = $rule;
            $fields = explode(",", $fields);
            $rules = explode(",", $rules);
            foreach ($fields as $field) {
                foreach ($rules as $r) {
                    if ($r == "req") {
                        $r = 'required';
                    }
                    if (! method_exists($this, $r)) {
                        $this->message = "{$r} is not a valid rule!";
                        return false;
                    }
                    try {
                        if ($r != 'required' && !$this->required($field)) {
                            continue;
                        }
                        if (! $this->{$r}($field)) {
                            $this->message = "{$r} fail : {$field} !";
                            return false;
                        }
                    }catch (\Exception $exception){
                        $this->message = $exception->getMessage();
                        return false;
                    }

                }
            }
        }
        return true;
    }
    public function getMessage() : string
    {
        return $this->message;
    }

    public function required(string $field) : bool
    {
        return !is_null($this->getValue($field));
    }

    public function string($field) : bool
    {
        return gettype($this->getValue($field)) == "string";
    }

    public function double(string $field) : bool
    {
        return gettype($this->getValue($field)) == "double";
    }

    public function bool(string $field) : bool
    {
        return gettype($this->getValue($field)) == "boolean";
    }

    public function int(string $field) : bool
    {
        return gettype($this->getValue($field)) == "integer";
    }

    public function list(string $field) : bool
    {
        return gettype($this->getValue($field)) == "array";
    }
    public function object(string $field) : bool
    {
        return gettype($this->getValue($field)) == "object";
    }

    public function null(string $field) : bool
    {
        return gettype($this->getValue($field)) == "NULL";
    }

    public function date(string $field) : bool
    {
        if(strtotime($this->getValue($field) ?: "")){
            return true;
        }
        return false;
    }

    public function empty(string $field) : bool
    {
        return !$this->getValue($field);
    }

    public function notEmpty(string $field) : bool
    {
        return (bool)$this->getValue($field);
    }

    public function getValue(string $field) : mixed
    {
        $has = function ($data, $keys) use (&$has){
            $key = array_shift($keys);
            if (is_array($data)) {
                if (! array_key_exists($key, $data)) {
                    return null;
                }
                if (!$keys) {
                    return $data[$key];
                }
                return $has($data[$key], $keys);
            }
            if (is_object($data)) {
                if (! property_exists($data, $key)) {
                    return null;
                }
                if (!$keys) {
                    return $data->{$key};
                }
                return $has($data->{$key}, $keys);
            }
            throw new \Exception("{$key} value must be an array or object");
        };
        return $has($this->respData, explode(".", $field));
    }
}