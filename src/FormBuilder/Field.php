<?php

namespace Fram\FormBuilder;

abstract class Field
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $required;

    /**
     * @var string[]
     */
    protected $attributes = [];

    /**
     * Specify the id.
     * 
     * @param string $id 
     * @return Input
     */
    public function id(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Specify the class.
     * 
     * @param string $class 
     * @return Input
     */
    public function class(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Specify the name.
     *
     * @param string $name 
     * @return Input
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Specify a custom attribute.
     *
     * @param string $attribute
     * @param string $value 
     * @return Input
     */
    public function attribute(string $attribute, string $value): self
    {
        $this->attributes[$attribute] = $value;

        return $this;
    }

    /**
     * Specify that the input is required.
     * 
     * @return Input
     */
    public function required(): self
    {
        $this->required = true;

        return $this;
    }

    /**
     * Add the attribute to the stringified input.
     * 
     * @param string $attribute
     */
    protected function addAttribute(string $attribute): string
    {
        if ($this->$attribute) {
            return $attribute . '="' . htmlspecialchars($this->$attribute ?? '') . '" ';
        }

        return '';
    }

    protected function addCustomAttributes(): string
    {
        $str = '';
        foreach ($this->attributes as $attribute => $value) {
            $str .= $attribute . '="' . $value . '" ';
        }

        return $str;
    }
}
