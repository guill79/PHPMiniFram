<?php

namespace Fram\Routing;

class InvalidArgumentsError extends \TypeError
{
    protected $message = 'The method %s of %s expects an attribute \'%s\' which does not exist in the request.';

    public function __construct(string $methodName, string $class, string $attribute)
    {
        parent::__construct(sprintf($this->message, $methodName, $class, $attribute), 1);
    }
}
