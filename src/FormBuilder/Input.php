<?php

namespace Fram\FormBuilder;

/**
 * Represents an HTML input field.
 */
class Input extends Field
{
    /**
     * @var String
     */
    protected $type;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $spellcheck;

    /**
     * @var bool
     */
    protected $autocomplete;

    /**
     * @var bool
     */
    protected $checked;

    /**
     * Constructor.
     * 
     * @param string $type The type of the input field.
     * @param array $config The configuration array.
     */
    public function __construct(string $type, array $config = [])
    {
        $this->type($type);
        $this->spellcheck($config['spellcheck'] ?? false);
        $this->autocomplete($config['autocomplete'] ?? false);
        $this->required = isset($config['required']) ?: false;
    }

    /**
     * Specify the type of the input.
     *
     * @param string $type 
     * @return Input
     */
    public function type(string $type): self
    {
        if (!in_array($type, ['text', 'submit', 'hidden', 'checkbox'])) {
            throw new \Exception('Invalid type');
        }
        $this->type = $type;

        return $this;
    }

    /**
     * Specify the value.
     * 
     * @param string $value 
     * @return Input
     */
    public function value(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Specify if checked.
     * 
     * @param bool $checked 
     * @return Input
     */
    public function checked(bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Specify the spellcheck.
     * 
     * @param bool $spellcheck 
     * @return Input
     */
    public function spellcheck(bool $spellcheck): self
    {
        $this->spellcheck = $spellcheck;

        return $this;
    }

    /**
     * Specify the autocomplete.
     * 
     * @param bool $autocomplete
     * @return Input
     */
    public function autocomplete(bool $autocomplete): self
    {
        $this->autocomplete = $autocomplete;

        return $this;
    }

    public function __toString()
    {
        $str = '<input type="' . $this->type . '" ';

        $str .= $this->addAttribute('class');
        $str .= $this->addAttribute('id');
        $str .= $this->addAttribute('name');
        $str .= $this->addAttribute('value');
        $str .= $this->addCustomAttributes();

        if ($this->type != 'submit') {
            $str .= 'autocomplete="' . ($this->autocomplete ? 'on' : 'off') . '" ';
            $str .= 'spellcheck="' . ($this->spellcheck ? 'true' : 'false') . '" ';
            if ($this->checked && $this->type == 'checkbox') $str .= 'checked ';
            if ($this->required) $str .= 'required ';
        }

        return $str . '/>';
    }
}
