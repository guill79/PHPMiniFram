<?php

namespace Fram\FormBuilder;

class FormBuilder
{
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function input(string $type): Input
    {
        return new Input($type, $this->config['input'] ?? []);
    }

    public function textarea(): Textarea
    {
        return new Textarea($this->config['textarea'] ?? []);
    }
}
